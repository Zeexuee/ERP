<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_product_id',
        'sku',
        'barcode',
        'qr_code',
        'name',
        'production_branch',
        'description',
        'category',
        'unit',
        'weight',
        'dimensions',
        'min_stock',
        'max_stock',
        'unit_price',
        'warehouse_branch_id',
        'warehouse_location_id',
        'current_stock',
        'pic_name',
        'signature_path',
        'batch_number',
        'expiry_date',
        'custom_fields',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'current_stock' => 'integer',
        'expiry_date' => 'date',
        'custom_fields' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (WarehouseItem $item) {
            $code = $item->branch?->code ?: $item->production_branch;
            if (empty($item->sku)) {
                $item->sku = static::generateSKU($code);
            }
            if (empty($item->barcode)) {
                $item->barcode = $item->sku;
            }
            if (empty($item->qr_code)) {
                $item->qr_code = $item->sku;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class, 'warehouse_product_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(WarehouseBranch::class, 'warehouse_branch_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(WarehouseStockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(WarehouseStockAdjustment::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    public function isOverStock(): bool
    {
        return $this->max_stock && $this->current_stock >= $this->max_stock;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->lte(now()->addDays($days));
    }

    public function isExpired(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->lt(now());
    }

    public function getStockValueAttribute(): float
    {
        return $this->current_stock * $this->unit_price;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        }

        if ($this->isLowStock()) {
            return 'low_stock';
        }

        if ($this->isOverStock()) {
            return 'over_stock';
        }

        return 'in_stock';
    }

    public static function generateSKU(?string $branchCode = null): string
    {
        $clean = $branchCode ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $branchCode)) : null;
        $prefix = $clean ? "ITM-{$clean}-" : 'ITM-';

        $lastItem = static::withTrashed()
            ->where('sku', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($lastItem && preg_match('/-(\d+)$/', $lastItem->sku, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        do {
            $sku = $prefix.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            $nextNum++;
        } while (static::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    public static function generateBarcode(?string $branchCode = null): string
    {
        return static::generateSKU($branchCode);
    }

    public static function generateQRCode(?string $branchCode = null): string
    {
        return static::generateSKU($branchCode);
    }

    public function addStock(
        int $quantity,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $picName = null,
        ?string $signaturePath = null
    ): void {
        $stockBefore = $this->current_stock;
        $this->current_stock += $quantity;
        $this->save();

        $this->stockMovements()->create([
            'warehouse_branch_id' => $this->warehouse_branch_id,
            'warehouse_location_id' => $this->warehouse_location_id,
            'type' => 'in',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $this->current_stock,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'pic_name' => $picName ?: $this->pic_name,
            'signature_path' => $signaturePath ?: $this->signature_path,
            'user_id' => auth()->id() ?: 1,
            'movement_date' => now(),
        ]);
    }

    public function removeStock(
        int $quantity,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $picName = null,
        ?string $signaturePath = null
    ): void {
        if ($quantity > $this->current_stock) {
            throw new \Exception("Insufficient stock. Available: {$this->current_stock}, Requested: {$quantity}");
        }

        $stockBefore = $this->current_stock;
        $this->current_stock -= $quantity;
        $this->save();

        $this->stockMovements()->create([
            'warehouse_branch_id' => $this->warehouse_branch_id,
            'warehouse_location_id' => $this->warehouse_location_id,
            'type' => 'out',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $this->current_stock,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'pic_name' => $picName ?: $this->pic_name,
            'signature_path' => $signaturePath ?: $this->signature_path,
            'user_id' => auth()->id() ?: 1,
            'movement_date' => now(),
        ]);
    }

    public function adjustStock(int $newQuantity, string $reason, ?string $notes = null): void
    {
        $quantityBefore = $this->current_stock;
        $adjustment = $newQuantity - $quantityBefore;

        $this->current_stock = $newQuantity;
        $this->save();

        $this->stockAdjustments()->create([
            'warehouse_branch_id' => $this->warehouse_branch_id,
            'reason' => $reason,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $newQuantity,
            'adjustment_quantity' => $adjustment,
            'notes' => $notes,
            'user_id' => auth()->id(),
            'adjustment_date' => now(),
        ]);

        $this->stockMovements()->create([
            'warehouse_branch_id' => $this->warehouse_branch_id,
            'warehouse_location_id' => $this->warehouse_location_id,
            'type' => 'adjustment',
            'quantity' => abs($adjustment),
            'stock_before' => $quantityBefore,
            'stock_after' => $newQuantity,
            'notes' => "Adjustment: {$reason}".($notes ? " - {$notes}" : ''),
            'user_id' => auth()->id(),
            'movement_date' => now(),
        ]);
    }
}
