<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'region_id', 'representative_id', 'quantity', 'amount', 'sale_date'];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }
}
