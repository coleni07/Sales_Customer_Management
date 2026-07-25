<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['customer_code', 'name', 'email', 'phone', 'address', 'location', 'total_orders', 'status'];

    /**
     * Masked phone number for list views, e.g. "0917-***".
     * Keeps only the digits before the first separator visible.
     */
    public function getPhoneMaskedAttribute(): string
    {
        if (! $this->phone) {
            return '—';
        }

        $digits = preg_replace('/\D/', '', $this->phone);
        $prefix = substr($digits, 0, 3);

        return $prefix !== '' ? "{$prefix}-***" : '***';
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
