<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportFeedback extends Model
{
    protected $table = 'support_feedbacks';

    protected $fillable = [
        'ticket_id',
        'title',
        'description',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
