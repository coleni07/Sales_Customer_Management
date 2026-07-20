<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'type',
        'objective',
        'description',
        'channel',
        'audience',
        'subject_line',
        'message',
        'media_path',
        'send_date',
        'send_time',
        'timezone',
        'status',
    ];

    protected $casts = [
        'send_date' => 'date:M j, Y',
    ];
}
