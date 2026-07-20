<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'customer_name',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
    ];

    public function feedbacks()
    {
        return $this->hasMany(SupportFeedback::class, 'ticket_id');
    }

    /**
     * Human-friendly code shown in the UI, e.g. TK-003.
     */
    public function code(): string
    {
        return 'TK-'.str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'High' => 'bg-rose-100 text-rose-700',
            'Medium' => 'bg-amber-100 text-amber-700',
            default => 'bg-emerald-100 text-emerald-700',
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'Open' => 'bg-rose-100 text-rose-700',
            'In Progress' => 'bg-amber-100 text-amber-700',
            default => 'bg-emerald-100 text-emerald-700',
        };
    }
}
