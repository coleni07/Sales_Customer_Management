<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_no', 'customer_id', 'subject', 'status'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'open' => 'bg-emerald-500 text-white',
            'in_progress' => 'bg-amber-400 text-white',
            'closed' => 'bg-slate-400 text-white',
            default => 'bg-slate-400 text-white',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }
}
