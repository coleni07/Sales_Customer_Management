<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Workflow;
use Illuminate\Http\Request;

class McmController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $campaignsQuery = Campaign::latest();

        if (in_array($status, ['draft', 'scheduled'])) {
            $campaignsQuery->where('status', $status);
        }

        $campaigns = $campaignsQuery->get();
        $workflows = Workflow::latest()->get();

        $draftCount = Campaign::where('status', 'draft')->count();
        $scheduledCount = Campaign::where('status', 'scheduled')->count();
        $allCount = $draftCount + $scheduledCount;

        return view('mcm.index', compact(
            'campaigns', 'workflows', 'status', 'draftCount', 'scheduledCount', 'allCount'
        ));
    }
}