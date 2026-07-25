<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['customer_code', 'name', 'email', 'phone', 'address', 'location', 'total_orders', 'status'];

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

    /**
     * Phone number masked for table/list display, e.g. "0912-***".
     * Keeps the first 4 digits visible and hides the rest.
     */
    public function getMaskedPhoneAttribute(): string
    {
        if (empty($this->phone)) {
            return 'N/A';
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 4) . '-***';
    }
}