<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, SalesOrder::class);
    }

    /**
     * Get all invoices related to this customer through sales orders.
     */
    public function getAllInvoices()
    {
        return $this->salesOrders->flatMap(function ($so) {
            return $so->invoices->each(function ($inv) use ($so) {
                $inv->setRelation('salesOrder', $so);
            });
        });
    }

    /**
     * Get outstanding (unpaid or partial) invoices.
     */
    public function getOutstandingInvoices()
    {
        return $this->getAllInvoices()->filter(function ($invoice) {
            return in_array($invoice->status, [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL]);
        });
    }

    /**
     * Total outstanding amount yet to be paid.
     */
    public function getTotalOutstandingBalanceAttribute(): float
    {
        return (float) $this->getOutstandingInvoices()->sum(function ($invoice) {
            return $invoice->remaining_amount;
        });
    }

    /**
     * Total purchases amount across all sales orders.
     */
    public function getTotalPurchasesAmountAttribute(): float
    {
        return (float) $this->salesOrders->sum('total_amount');
    }
}
