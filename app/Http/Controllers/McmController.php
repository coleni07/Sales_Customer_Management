<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Workflow;

class McmController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->get();
        $workflows = Workflow::latest()->get();

        return view('mcm.index', compact('campaigns', 'workflows'));
    }
}