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

    /**
     * Alias kept for compatibility with existing views/controllers
     * that reference $customer->orders — this points at the real
     * SalesOrder model (there is no separate "orders" table).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Full phone number formatted in the Philippine style: 0917-123-4567.
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (empty($this->phone)) {
            return 'N/A';
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return substr($digits, 0, 4) . '-' . substr($digits, 4, 3) . '-' . substr($digits, 7);
        }

        return $this->phone;
    }

    /**
     * Phone number masked for table/list display, e.g. "0917-***".
     */
    public function getMaskedPhoneAttribute(): string
    {
        if (empty($this->phone)) {
            return 'N/A';
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 4) . '-***';
    }
}
