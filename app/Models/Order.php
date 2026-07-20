<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Sum of only the delivered/completed items in this order.
     */
    public function getCompletedTotalAttribute(): float
    {
        return $this->items()
            ->where('status', 'delivered')
            ->get()
            ->sum(fn ($item) => $item->price * $item->quantity);
    }
}
