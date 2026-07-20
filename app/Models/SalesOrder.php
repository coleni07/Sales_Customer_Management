<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'customer_id', 'subtotal', 'discount_label', 'discount_amount',
        'tax_label', 'tax_amount', 'shipping_fee', 'amount', 'status',
        'payment_method', 'approval_status', 'warehouse_code', 'gl_code', 'order_date',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    // Badge color helpers used in Blade views
    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-400 text-white',
            'processing' => 'bg-blue-500 text-white',
            'shipped' => 'bg-violet-500 text-white',
            'delivered' => 'bg-emerald-500 text-white',
            'cancelled' => 'bg-rose-500 text-white',
            default => 'bg-slate-400 text-white',
        };
    }

    public function paymentLabel(): string
    {
        return match ($this->payment_method) {
            'cod' => 'COD',
            'credit' => 'Credit',
            'debit' => 'Debit',
            default => ucfirst($this->payment_method),
        };
    }
}
