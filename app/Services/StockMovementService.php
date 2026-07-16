<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\ItemJournal;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;

class StockMovementService
{
    public function __construct(private NumberGeneratorService $numGen) {}

    /**
     * Tambah stok (Stock In / Transfer In)
     * Wajib dipanggil dalam DB::transaction()
     */
    public function in(
        int    $productId,
        int    $warehouseId,
        float  $qty,
        string $type,           // 'Stock In' | 'Transfer In'
        string $reference,
        string $description
    ): void {
        Stock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        )->increment('quantity', $qty);

        $this->journal($productId, $warehouseId, $qty, $type, $reference, $description);
    }

    /**
     * Kurangi stok (Stock Out / Transfer Out)
     * Wajib dipanggil dalam DB::transaction()
     */
    public function out(
        int    $productId,
        int    $warehouseId,
        float  $qty,
        string $type,           // 'Stock Out' | 'Transfer Out'
        string $reference,
        string $description
    ): void {
        $stock = Stock::where('product_id', $productId)
                      ->where('warehouse_id', $warehouseId)
                      ->lockForUpdate()
                      ->first();

        if (!$stock || $stock->quantity < $qty) {
            throw new \Exception(
                "Stok produk ID {$productId} di gudang ID {$warehouseId} tidak mencukupi."
            );
        }

        $stock->decrement('quantity', $qty);
        $this->journal($productId, $warehouseId, $qty, $type, $reference, $description);
    }

    private function journal(
        int $productId, int $warehouseId, float $qty,
        string $type, string $ref, string $desc
    ): void {
        ItemJournal::create([
            'journal_number'   => $this->numGen->generate('JRN', ItemJournal::class, 'journal_number'),
            'transaction_type' => $type,
            'product_id'       => $productId,
            'warehouse_id'     => $warehouseId,
            'quantity'         => $qty,
            'description'      => $desc,
            'reference_number' => $ref,
            'transaction_date' => now()->toDateString(),
        ]);
    }
}
