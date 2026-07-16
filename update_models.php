<?php
$models = [
    'Product' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = ['product_code', 'product_name', 'product_type', 'unit_id', 'description', 'created_by'];

    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
EOT,
    'Supplier' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = ['supplier_name', 'supplier_address', 'supplier_phone', 'supplier_email', 'bank_account'];
}
EOT,
    'Unit' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['unit_name', 'description'];
}
EOT,
    'Project' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;
    protected $fillable = ['project_name', 'project_address', 'person_in_charge'];
}
EOT,
    'Warehouse' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;
    protected $fillable = ['warehouse_name', 'description'];
}
EOT,
    'PurchaseOrder' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    use SoftDeletes;
    protected $fillable = ['po_number','supplier_id','po_date','project_note','total_amount','status','created_by'];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function details(): HasMany { return $this->hasMany(PurchaseOrderDetail::class); }
    public function goodsReceipts(): HasMany { return $this->hasMany(GoodsReceipt::class); }
    public function payment(): HasOne { return $this->hasOne(PurchasePayment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->details()->sum('subtotal')]);
    }
}
EOT,
    'PurchaseOrderDetail' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'unit_id', 'quantity', 'unit_price', 'subtotal'];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
}
EOT,
    'GoodsReceipt' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    protected $fillable = ['gr_number', 'purchase_order_id', 'warehouse_id', 'received_by', 'receipt_date'];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function details(): HasMany { return $this->hasMany(GoodsReceiptDetail::class); }
}
EOT,
    'GoodsReceiptDetail' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDetail extends Model
{
    protected $fillable = ['goods_receipt_id', 'product_id', 'quantity_order', 'quantity_received'];

    public function goodsReceipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
EOT,
    'PurchasePayment' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePayment extends Model
{
    protected $fillable = ['purchase_order_id', 'total_purchase', 'total_paid', 'remaining_payment', 'payment_status', 'payment_date'];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
}
EOT,
    'BillOfMaterial' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillOfMaterial extends Model
{
    use SoftDeletes;
    protected $fillable = ['bom_number', 'product_id', 'description', 'created_by'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function details(): HasMany { return $this->hasMany(BillOfMaterialDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
EOT,
    'BillOfMaterialDetail' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillOfMaterialDetail extends Model
{
    protected $fillable = ['bill_of_material_id', 'raw_material_id', 'quantity', 'unit_id'];

    public function billOfMaterial(): BelongsTo { return $this->belongsTo(BillOfMaterial::class); }
    public function rawMaterial(): BelongsTo { return $this->belongsTo(Product::class, 'raw_material_id'); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
}
EOT,
    'ProductionOrder' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class ProductionOrder extends Model
{
    use SoftDeletes;
    protected $fillable = ['production_number', 'product_id', 'bill_of_material_id', 'quantity_target', 'production_date', 'person_in_charge', 'status', 'notes', 'created_by'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function billOfMaterial(): BelongsTo { return $this->belongsTo(BillOfMaterial::class); }
    public function materialIssues(): HasMany { return $this->hasMany(MaterialIssue::class); }
    public function result(): HasOne { return $this->hasOne(ProductionResult::class); }

    public function getMaterialRequirements(): Collection
    {
        return $this->billOfMaterial->details->map(fn($d) => [
            'raw_material' => $d->rawMaterial,
            'unit' => $d->unit,
            'quantity_bom' => $d->quantity,
            'quantity_required' => $this->quantity_target * $d->quantity,
        ]);
    }
}
EOT,
    'MaterialIssue' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialIssue extends Model
{
    protected $fillable = ['issue_number', 'production_order_id', 'warehouse_id', 'issue_date', 'issued_by', 'notes'];

    public function productionOrder(): BelongsTo { return $this->belongsTo(ProductionOrder::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function details(): HasMany { return $this->hasMany(MaterialIssueDetail::class); }
}
EOT,
    'MaterialIssueDetail' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialIssueDetail extends Model
{
    protected $fillable = ['material_issue_id', 'raw_material_id', 'quantity_required', 'quantity_issued', 'unit_id'];

    public function materialIssue(): BelongsTo { return $this->belongsTo(MaterialIssue::class); }
    public function rawMaterial(): BelongsTo { return $this->belongsTo(Product::class, 'raw_material_id'); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
}
EOT,
    'ProductionResult' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionResult extends Model
{
    protected $fillable = ['result_number', 'production_order_id', 'warehouse_id', 'result_date', 'quantity_target', 'quantity_finished', 'quantity_reject', 'notes'];

    public function productionOrder(): BelongsTo { return $this->belongsTo(ProductionOrder::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
EOT,
    'Stock' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
EOT,
    'StockOpname' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpname extends Model
{
    protected $fillable = ['warehouse_id', 'product_id', 'system_stock', 'physical_stock', 'difference', 'notes', 'opname_date'];

    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
EOT,
    'InventoryTransfer' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransfer extends Model
{
    protected $fillable = ['transfer_number', 'source_warehouse_id', 'destination_warehouse_id', 'transfer_date', 'notes', 'created_by'];

    public function sourceWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'source_warehouse_id'); }
    public function destinationWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'destination_warehouse_id'); }
    public function details(): HasMany { return $this->hasMany(InventoryTransferDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
EOT,
    'InventoryTransferDetail' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransferDetail extends Model
{
    protected $fillable = ['inventory_transfer_id', 'product_id', 'quantity'];

    public function inventoryTransfer(): BelongsTo { return $this->belongsTo(InventoryTransfer::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
EOT,
    'ItemJournal' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemJournal extends Model
{
    protected $fillable = ['journal_number', 'transaction_type', 'product_id', 'warehouse_id', 'quantity', 'description', 'reference_number', 'transaction_date'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
EOT,
];

foreach ($models as $name => $content) {
    file_put_contents(__DIR__ . "/app/Models/{$name}.php", $content);
    echo "Updated $name\n";
}
