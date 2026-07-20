<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'trigger',
        'status',
        'action',
        'audience',
        'description',
        'leads_enrolled',
    ];
}
