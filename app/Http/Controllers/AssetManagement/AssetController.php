<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Excel\Exports\MasterAssetExport;
use App\Excel\Exports\MasterAssetErrorExport;
use App\Excel\Imports\MasterAssetImport;
use App\Excel\Templates\MasterAssetTemplateExport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AssetController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Asset::class, strtolower('Asset'));
    }
    public function index()
    {
        $assets = Asset::with('category')->get();
        return view('asset_management.assets.index', compact('assets'));
    }

    public function create()
    {
        $categories = AssetCategory::all();
        return view('asset_management.assets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'asset_description' => 'nullable|string',
            'location' => 'nullable|string',
            'department' => 'nullable|string',
            'responsible_person' => 'nullable|string',
            'purchase_date' => 'required|date',
            'start_depreciation_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'residual_value' => 'required|numeric|min:0',
            'commercial_method' => 'required|string',
            'commercial_useful_life' => 'required|integer|min:1',
            'fiscal_method' => 'required|string',
            'fiscal_useful_life' => 'required|integer|min:1',
            'vendor' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        $year = Carbon::now()->format('Y');
        $count = Asset::whereYear('created_at', $year)->count() + 1;
        $validated['asset_code'] = 'AST-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        $validated['status'] = 'Active';

        Asset::create($validated);
        return redirect()->route('asset-management.assets.index')->with('success', 'Asset created successfully');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'maintenances', 'improvements', 'movements']);

        $commercialDepreciations = $asset->commercial_schedule;
        $fiscalDepreciations = $asset->fiscal_schedule;

        return view('asset_management.assets.show', compact('asset', 'commercialDepreciations', 'fiscalDepreciations'));
    }

    public function edit(Asset $asset)
    {
        $categories = AssetCategory::all();
        return view('asset_management.assets.edit', compact('asset', 'categories'));
    }

    public function update(Request $request, Asset $asset)
    {
        $asset->update($request->all());
        return redirect()->route('asset-management.assets.index')->with('success', 'Asset updated successfully');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);
        $asset->delete();
        return redirect()->route('asset-management.assets.index')->with('success', 'Asset deleted successfully');
    }

    public function storeMaintenance(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        
        $asset->maintenances()->create($validated);
        return back()->with('success', 'Maintenance record added successfully');
    }

    public function storeImprovement(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'improvement_date' => 'required|date',
            'improvement_cost' => 'required|numeric|min:0',
            'vendor' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        
        // These fields are required by DB schema but we now dynamically calculate them.
        // We will just store the snapshot of what they are TODAY, but the engine doesn't rely on it.
        $validated['previous_book_value_commercial'] = $asset->commercial_book_value;
        $validated['new_book_value_commercial'] = $asset->commercial_book_value + $validated['improvement_cost'];
        $validated['previous_book_value_fiscal'] = $asset->fiscal_book_value;
        $validated['new_book_value_fiscal'] = $asset->fiscal_book_value + $validated['improvement_cost'];
        
        $asset->improvements()->create($validated);
        return back()->with('success', 'Capital Improvement recorded. Depreciation base has been updated.');
    }

    public function storeMovement(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'movement_date' => 'required|date',
            'from_department' => 'nullable|string',
            'to_department' => 'required|string',
            'from_location' => 'nullable|string',
            'to_location' => 'required|string',
            'from_pic' => 'nullable|string',
            'to_pic' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        
        $asset->movements()->create($validated);
        
        $asset->update([
            'department' => $validated['to_department'],
            'location' => $validated['to_location'],
            'responsible_person' => $validated['to_pic']
        ]);

        return back()->with('success', 'Asset movement recorded successfully.');
    }

    // --- Enterprise Import Module ---

    public function export(Request $request)
    {
        $this->authorize('viewAny', Asset::class);
        // Respect current filters if there are any
        $query = Asset::with('category');
        // Simple search example if standard search is used in index
        if ($search = $request->search) {
            $query->where('asset_name', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%");
        }
        
        return Excel::download(new MasterAssetExport($query->get()), 'master_asset_export.xlsx');
    }

    public function downloadTemplate()
    {
        $this->authorize('create', Asset::class);
        return Excel::download(new MasterAssetTemplateExport, 'master_asset_template.xlsx');
    }

    public function uploadImport(Request $request)
    {
        $this->authorize('create', Asset::class);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new MasterAssetImport();
        Excel::import($import, $request->file('file'));

        $data = $import->sheetImport->data ?? collect();
        
        if ($data->isEmpty()) {
            return back()->with('error', 'File Excel kosong atau format tidak sesuai.');
        }

        // Validate Headers
        $expectedHeaders = [
            'asset_code', 'asset_name', 'category', 'brand', 'model', 'serial_number', 
            'location', 'department', 'responsible_person', 'purchase_date', 
            'start_depreciation_date', 'acquisition_cost', 'residual_value', 
            'commercial_method', 'commercial_useful_life', 'fiscal_method', 'fiscal_useful_life'
        ];
        
        $firstRowHeaders = array_keys($data->first()->toArray());
        
        $missingHeaders = [];
        foreach ($expectedHeaders as $header) {
            if (!in_array($header, $firstRowHeaders)) {
                $missingHeaders[] = $header;
            }
        }
        
        if (count($missingHeaders) > 0) {
            return back()->with('error', 'Invalid Template: Header mismatch. Missing: ' . implode(', ', $missingHeaders));
        }

        $parsedRows = [];
        $createCount = 0;
        $updateCount = 0;
        $invalidCount = 0;
        
        foreach ($data as $index => $row) {
            // Excel row number starts at 2 (1 is header)
            $rowNum = $index + 2; 
            
            // Skip empty rows
            if (empty(trim($row['asset_name'])) && empty(trim($row['category'])) && empty(trim($row['acquisition_cost']))) {
                continue;
            }

            $errors = [];
            $suggestedFix = [];

            // Required validations
            if (empty(trim($row['asset_name']))) $errors[] = "Asset Name is required.";
            if (empty(trim($row['category']))) $errors[] = "Category is required.";
            if (empty(trim($row['purchase_date']))) $errors[] = "Purchase Date is required.";
            if (empty(trim($row['acquisition_cost']))) $errors[] = "Acquisition Cost is required.";

            // Case Insensitive Category Lookup
            $categoryNameRaw = trim($row['category']);
            $category = AssetCategory::where(DB::raw('lower(category_name)'), strtolower($categoryNameRaw))->first();
            if (!$category) {
                $errors[] = "Category not found.";
                $suggestedFix[] = "Check Reference Sheet for exact names.";
            }

            // Standardize string lookups
            $department = ucwords(strtolower(trim($row['department'])));
            $location = ucwords(strtolower(trim($row['location'])));
            $picRaw = trim($row['responsible_person']);
            $pic = $picRaw;

            // Try resolving PIC by email or name if it exists in a User/Employee table (Mocking basic fallback)
            if (!empty($picRaw)) {
                $user = User::where('email', $picRaw)->orWhere(DB::raw('lower(name)'), strtolower($picRaw))->first();
                if ($user) {
                    $pic = $user->name; // Use standardized name
                }
            }

            // Convert Dates correctly (Handling Excel Serial Dates if needed)
            $purchaseDate = null;
            try {
                if (is_numeric($row['purchase_date'])) {
                    $purchaseDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['purchase_date'])->format('Y-m-d');
                } else {
                    $purchaseDate = Carbon::createFromFormat('d/m/Y', trim($row['purchase_date']))->format('Y-m-d');
                }
            } catch (\Exception $e) {
                if (!empty($row['purchase_date'])) {
                    try {
                        $purchaseDate = Carbon::parse(trim($row['purchase_date']))->format('Y-m-d');
                    } catch (\Exception $ex) {
                        $errors[] = "Invalid Purchase Date.";
                        $suggestedFix[] = "Use format dd/mm/yyyy";
                    }
                }
            }

            $startDepDate = null;
            try {
                if (is_numeric($row['start_depreciation_date'])) {
                    $startDepDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_depreciation_date'])->format('Y-m-d');
                } else {
                    $startDepDate = Carbon::createFromFormat('d/m/Y', trim($row['start_depreciation_date']))->format('Y-m-d');
                }
            } catch (\Exception $e) {
                if (!empty($row['start_depreciation_date'])) {
                    try {
                        $startDepDate = Carbon::parse(trim($row['start_depreciation_date']))->format('Y-m-d');
                    } catch (\Exception $ex) {
                        $errors[] = "Invalid Start Depreciation Date.";
                        $suggestedFix[] = "Use format dd/mm/yyyy";
                    }
                }
            }

            // Asset Code Upsert Logic
            $assetCodeRaw = trim($row['asset_code']);
            $action = 'Create';
            $existingAsset = null;

            if (!empty($assetCodeRaw)) {
                $existingAsset = Asset::where('asset_code', $assetCodeRaw)->first();
                if ($existingAsset) {
                    $action = 'Update';
                }
            }

            $status = count($errors) > 0 ? 'Failed' : 'Ready';

            if ($status == 'Ready') {
                if ($action == 'Create') $createCount++;
                if ($action == 'Update') $updateCount++;
            } else {
                $invalidCount++;
            }

            $parsedRows[] = [
                'row_num' => $rowNum,
                'action' => $action,
                'status' => $status,
                'errors' => implode(', ', $errors),
                'suggested_fix' => implode(' | ', $suggestedFix),
                
                // Raw data for Error Report
                'raw' => $row->toArray(),

                // Parsed data for Insertion
                'parsed' => [
                    'asset_code' => $assetCodeRaw,
                    'asset_name' => trim($row['asset_name']),
                    'category_id' => $category->id ?? null,
                    'brand' => trim($row['brand']),
                    'model' => trim($row['model']),
                    'serial_number' => trim($row['serial_number']),
                    'location' => $location,
                    'department' => $department,
                    'responsible_person' => $pic,
                    'purchase_date' => $purchaseDate,
                    'start_depreciation_date' => $startDepDate,
                    'acquisition_cost' => trim($row['acquisition_cost']),
                    'residual_value' => trim($row['residual_value']),
                    'commercial_method' => trim($row['commercial_method']),
                    'commercial_useful_life' => trim($row['commercial_useful_life']),
                    'fiscal_method' => trim($row['fiscal_method']),
                    'fiscal_useful_life' => trim($row['fiscal_useful_life']),
                ]
            ];
        }

        $importId = 'IMP-AST-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        
        $sessionData = [
            'import_id' => $importId,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'expired_at' => now()->addMinutes(30),
            'total_rows' => count($parsedRows),
            'create_count' => $createCount,
            'update_count' => $updateCount,
            'invalid_rows' => $invalidCount,
            'data' => $parsedRows
        ];

        Cache::put('import_assets_' . $importId, $sessionData, now()->addMinutes(30));

        return view('asset_management.assets.import_preview', compact('sessionData'));
    }

    public function processImport(Request $request)
    {
        $this->authorize('create', Asset::class);
        $importId = $request->import_id;
        $sessionData = Cache::get('import_assets_' . $importId);

        if (!$sessionData) {
            return redirect()->route('asset-management.assets.index')->with('error', 'Import session expired. Silakan upload ulang.');
        }

        $validCreate = [];
        $validUpdate = [];
        $startTime = microtime(true);

        DB::beginTransaction();
        try {
            foreach ($sessionData['data'] as &$row) {
                if ($row['status'] == 'Ready') {
                    $p = $row['parsed'];

                    if ($row['action'] == 'Create') {
                        // Generate Asset Code if blank
                        $assetCode = $p['asset_code'];
                        if (empty($assetCode)) {
                            $year = Carbon::now()->format('Y');
                            $count = Asset::whereYear('created_at', $year)->count() + 1;
                            $assetCode = 'AST-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
                        }
                        $p['asset_code'] = $assetCode;
                        $p['status'] = 'Active';
                        
                        Asset::create($p);
                        $validCreate[] = $assetCode;
                    } 
                    else if ($row['action'] == 'Update') {
                        $asset = Asset::where('asset_code', $p['asset_code'])->first();
                        if ($asset) {
                            $asset->update($p);
                            $validUpdate[] = $p['asset_code'];
                        }
                    }

                    $row['status'] = 'Imported';
                }
            }
            DB::commit();

            Cache::put('import_assets_' . $importId, $sessionData, now()->addMinutes(30));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('asset-management.assets.index')->with('error', 'Terjadi kesalahan sistem saat import: ' . $e->getMessage());
        }

        $duration = number_format(microtime(true) - $startTime, 2);

        activity('bulk_import')
            ->performedOn(new Asset())
            ->causedBy(auth()->user())
            ->log("Bulk Imported Assets\nCreated : " . count($validCreate) . "\nUpdated : " . count($validUpdate) . "\nFailed : " . $sessionData['invalid_rows'] . "\nSource : Excel\nImport ID : $importId");

        return redirect()->route('asset-management.assets.index')->with('import_success', [
            'total' => $sessionData['total_rows'],
            'created' => count($validCreate),
            'updated' => count($validUpdate),
            'failed' => $sessionData['invalid_rows'],
            'duration' => $duration,
            'import_id' => $importId
        ]);
    }

    public function downloadErrors($id)
    {
        $this->authorize('create', Asset::class);
        $sessionData = Cache::get('import_assets_' . $id);
        
        if (!$sessionData) {
            return redirect()->route('asset-management.assets.index')->with('error', 'Error report expired.');
        }

        $errors = [];
        foreach ($sessionData['data'] as $row) {
            if ($row['status'] == 'Failed') {
                $r = $row['raw'];
                $errors[] = [
                    $r['asset_code'] ?? '',
                    $r['asset_name'] ?? '',
                    $r['category'] ?? '',
                    $r['brand'] ?? '',
                    $r['model'] ?? '',
                    $r['serial_number'] ?? '',
                    $r['location'] ?? '',
                    $r['department'] ?? '',
                    $r['responsible_person'] ?? '',
                    $r['purchase_date'] ?? '',
                    $r['start_depreciation_date'] ?? '',
                    $r['acquisition_cost'] ?? '',
                    $r['residual_value'] ?? '',
                    $r['commercial_method'] ?? '',
                    $r['commercial_useful_life'] ?? '',
                    $r['fiscal_method'] ?? '',
                    $r['fiscal_useful_life'] ?? '',
                    $row['errors'],
                    $row['suggested_fix']
                ];
            }
        }

        return Excel::download(new MasterAssetErrorExport($errors), 'asset_import_errors.xlsx');
    }
}
