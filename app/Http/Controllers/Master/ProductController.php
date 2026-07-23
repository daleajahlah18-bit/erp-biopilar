<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Excel\Templates\MasterProductTemplateExport;
use App\Excel\Imports\MasterProductImport;
use App\Excel\Exports\MasterProductErrorExport;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, strtolower('Product'));
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $query = Product::with(['unit', 'creator'])->sortable()->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('product_type', 'like', "%{$search}%")
                  ->orWhere('engineering_category', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(10)->appends($request->query());

        $recentImports = \Spatie\Activitylog\Models\Activity::where('log_name', 'bulk_import')
            ->where('subject_type', Product::class)
            ->latest()
            ->take(5)
            ->get();

        return view('master.products.index', compact('products', 'recentImports'));
    }

    public function create()
    {
        $units = Unit::all();
        return view('master.products.create', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'required|unique:products,product_code',
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|in:Bahan Baku,Bahan Jadi,Bill of Material',
            'engineering_category' => 'required|in:Civil,Mechanical,Electrical',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string'
        ]);

        Product::create([
            'product_code' => $request->product_code,
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'engineering_category' => $request->engineering_category,
            'unit_id' => $request->unit_id,
            'description' => $request->description,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('master.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['unit', 'creator']);
        return view('master.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $units = Unit::all();
        return view('master.products.edit', compact('product', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_code' => 'required|unique:products,product_code,' . $product->id,
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|in:Bahan Baku,Bahan Jadi,Bill of Material',
            'engineering_category' => 'required|in:Civil,Mechanical,Electrical',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string'
        ]);

        $product->update($request->only('product_code', 'product_name', 'product_type', 'engineering_category', 'unit_id', 'description'));

        return redirect()->route('master.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Free up the product code so it can be reused later
        $product->update([
            'product_code' => $product->product_code . '-deleted-' . time()
        ]);
        $product->delete();
        return redirect()->route('master.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    // --- Enterprise Import Module ---

    public function downloadTemplate()
    {
        $this->authorize('import', Product::class);
        return Excel::download(new MasterProductTemplateExport, 'master_product_template.xlsx');
    }

    public function uploadImport(Request $request)
    {
        $this->authorize('import', Product::class);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new MasterProductImport();
        Excel::import($import, $request->file('file'));

        $data = $import->sheetImport->data ?? collect();
        
        if ($data->isEmpty()) {
            return back()->with('error', 'File Excel kosong atau format tidak sesuai.');
        }

        // Validate Headers
        $expectedHeaders = ['product_code', 'product_name', 'product_type', 'engineering_category', 'unit', 'description'];
        $firstRowHeaders = array_keys($data->first()->toArray());
        
        $missingHeaders = [];
        foreach ($expectedHeaders as $header) {
            if (!in_array($header, $firstRowHeaders)) {
                $missingHeaders[] = $header;
            }
        }
        
        if (count($missingHeaders) > 0) {
            return back()->with('error', 'Invalid Template: Header mismatch. Missing: ' . implode(', ', $missingHeaders) . '. Found: ' . implode(', ', $firstRowHeaders));
        }

        $unitsMap = Unit::pluck('id', 'unit_name')->mapWithKeys(function ($item, $key) {
            return [strtolower($key) => $item];
        })->toArray();

        $existingActiveCodes = Product::pluck('product_code')->map(function($item) {
            return strtolower($item);
        })->toArray();

        $existingTrashedCodes = Product::onlyTrashed()->pluck('product_code')->map(function($item) {
            return strtolower($item);
        })->toArray();

        $typeMap = [
            'bahan baku' => 'Bahan Baku',
            'bahan jadi' => 'Bahan Jadi',
            'bill of material' => 'Bill of Material',
        ];

        $categoryMap = [
            'civil' => 'Civil',
            'mechanical' => 'Mechanical',
            'electrical' => 'Electrical'
        ];

        $parsedRows = [];
        $validCount = 0;
        $invalidCount = 0;
        $skippedCount = 0;
        $errorSummary = [];

        $intraFileCodes = [];

        // First pass for intra-file duplicates
        foreach ($data as $row) {
            if (empty($row['product_code'])) continue;
            $code = strtolower(trim($row['product_code']));
            if (!isset($intraFileCodes[$code])) {
                $intraFileCodes[$code] = 1;
            } else {
                $intraFileCodes[$code]++;
            }
        }

        foreach ($data as $index => $row) {
            $status = 'Ready';
            $errors = [];
            $isDuplicate = false;
            $duplicateMsg = '';

            // Ignore completely empty rows
            if (empty(array_filter($row->toArray()))) {
                continue;
            }

            $productCode = trim($row['product_code'] ?? '');
            $productName = trim($row['product_name'] ?? '');
            $productTypeRaw = strtolower(trim($row['product_type'] ?? ''));
            $engCategoryRaw = strtolower(trim($row['engineering_category'] ?? ''));
            $unitRaw = strtolower(trim($row['unit'] ?? ''));

            if (empty($productCode)) {
                $errors[] = "Product Code Required";
            } else {
                if (in_array(strtolower($productCode), $existingActiveCodes)) {
                    $isDuplicate = true;
                    $duplicateMsg = 'Already Exists (Skipped)';
                } elseif (in_array(strtolower($productCode), $existingTrashedCodes)) {
                    $isDuplicate = true;
                    $duplicateMsg = 'Already Exists in Trash (Skipped)';
                }
                
                if ($intraFileCodes[strtolower($productCode)] > 1) {
                    $errors[] = "Duplicate inside Excel";
                }
            }

            if (empty($productName)) {
                $errors[] = "Product Name Required";
            } elseif (strlen($productName) > 255) {
                $errors[] = "Product Name too long";
            }

            if (!array_key_exists($productTypeRaw, $typeMap)) {
                $errors[] = "Invalid Product Type";
            }

            if (!array_key_exists($engCategoryRaw, $categoryMap)) {
                $errors[] = "Invalid Engineering Category";
            }

            if (!array_key_exists($unitRaw, $unitsMap)) {
                $errors[] = "Unit not found";
            }

            if ($isDuplicate) {
                $status = 'Skipped';
                $skippedCount++;
            } elseif (count($errors) > 0) {
                $status = 'Failed';
                $invalidCount++;
                foreach ($errors as $error) {
                    $errorSummary[$error] = ($errorSummary[$error] ?? 0) + 1;
                }
            } else {
                $validCount++;
            }

            $parsedRows[] = [
                'status' => $status,
                'errors' => $isDuplicate ? $duplicateMsg : implode(", ", $errors),
                'product_code' => $productCode,
                'product_name' => $productName,
                'product_type_raw' => $row['product_type'] ?? '',
                'engineering_category_raw' => $row['engineering_category'] ?? '',
                'unit_raw' => $row['unit'] ?? '',
                'description' => $row['description'] ?? '',
                'product_type' => $typeMap[$productTypeRaw] ?? null,
                'engineering_category' => $categoryMap[$engCategoryRaw] ?? null,
                'unit_id' => $unitsMap[$unitRaw] ?? null,
            ];
        }

        $importId = 'IMP-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        
        $sessionData = [
            'import_id' => $importId,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'expired_at' => now()->addMinutes(30),
            'total_rows' => count($parsedRows),
            'valid_rows' => $validCount,
            'skipped_rows' => $skippedCount,
            'invalid_rows' => $invalidCount,
            'error_summary' => $errorSummary,
            'data' => $parsedRows
        ];

        Cache::put('import_products_' . $importId, $sessionData, now()->addMinutes(30));

        return view('master.products.import_preview', compact('sessionData'));
    }

    public function processImport(Request $request)
    {
        $this->authorize('import', Product::class);
        $importId = $request->import_id;
        $sessionData = Cache::get('import_products_' . $importId);

        if (!$sessionData) {
            return redirect()->route('master.products.index')->with('error', 'Import session expired. Silakan upload ulang.');
        }

        $validRowsToInsert = [];
        $startTime = microtime(true);

        foreach ($sessionData['data'] as $row) {
            if ($row['status'] == 'Ready') {
                $validRowsToInsert[] = [
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'product_type' => $row['product_type'],
                    'engineering_category' => $row['engineering_category'],
                    'unit_id' => $row['unit_id'],
                    'description' => $row['description'],
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($validRowsToInsert) == 0) {
            return redirect()->route('master.products.index')->with('error', 'Tidak ada data valid untuk diimport.');
        }

        DB::beginTransaction();
        try {
            Product::insert($validRowsToInsert);
            DB::commit();

            // Prevent double-insertion if user refreshes the page by clearing valid rows from cache
            foreach ($sessionData['data'] as &$row) {
                if ($row['status'] == 'Ready') {
                    $row['status'] = 'Imported';
                }
            }
            Cache::put('import_products_' . $importId, $sessionData, now()->addMinutes(30));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('master.products.index')->with('error', 'Terjadi kesalahan sistem saat import: ' . $e->getMessage());
        }

        $duration = number_format(microtime(true) - $startTime, 2);

        activity('bulk_import')
            ->performedOn(new Product())
            ->causedBy(auth()->user())
            ->log("Bulk Imported Products\nImported : " . count($validRowsToInsert) . "\nSkipped : 0\nFailed : " . $sessionData['invalid_rows'] . "\nSource : Excel\nImport ID : $importId");

        // We can keep the cache so they can download errors from the index page.
        // Cache::forget('import_products_' . $importId);

        return redirect()->route('master.products.index')->with('import_success', [
            'total' => $sessionData['total_rows'],
            'imported' => count($validRowsToInsert),
            'skipped' => $sessionData['skipped_rows'],
            'failed' => $sessionData['invalid_rows'],
            'duration' => $duration,
            'import_id' => $importId
        ]);
    }

    public function downloadErrors($id)
    {
        $this->authorize('import', Product::class);
        $sessionData = Cache::get('import_products_' . $id);
        
        if (!$sessionData) {
            return redirect()->route('master.products.index')->with('error', 'Error report expired.');
        }

        $errors = [];
        foreach ($sessionData['data'] as $row) {
            if ($row['status'] == 'Failed') {
                $errors[] = [
                    $row['product_code'],
                    $row['product_name'],
                    $row['product_type_raw'],
                    $row['engineering_category_raw'],
                    $row['unit_raw'],
                    $row['description'],
                    $row['errors']
                ];
            }
        }

        return Excel::download(new MasterProductErrorExport($errors), 'product_import_errors.xlsx');
    }
}
