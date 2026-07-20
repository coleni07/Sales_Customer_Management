<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Representative extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'region_id', 'monthly_quota'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
