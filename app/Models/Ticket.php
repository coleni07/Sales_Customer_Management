<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'customer_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to'
    ];

    public function getCustomerNameAttribute(): string
    {
        return $this->customer->name ?? 'Unknown Customer';
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(SupportFeedback::class, 'ticket_id');
    }

    public function code(): string
    {
        return $this->ticket_no;
    }

    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'high' => 'bg-rose-100 text-rose-700',
            'medium' => 'bg-amber-100 text-amber-700',
            default => 'bg-emerald-100 text-emerald-700',
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'open' => 'bg-rose-100 text-rose-700',
            'in_progress' => 'bg-amber-100 text-amber-700',
            default => 'bg-emerald-100 text-emerald-700',
        };
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

    public function priorityLabel(): string
    {
        return ucfirst($this->priority);
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