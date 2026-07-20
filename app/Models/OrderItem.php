<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'store_name',
        'quantity',
        'price',
        'icon',
        'status',
        'expected_delivery',
    ];

    protected $casts = [
        'expected_delivery' => 'date',
        'price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}