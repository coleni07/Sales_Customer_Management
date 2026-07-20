<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function create()
    {
        return view('workflows.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'trigger'     => ['required', 'string', 'max:255'],
            'status'      => ['required', 'in:active,paused'],
            'action'      => ['required', 'string', 'max:255'],
            'audience'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Workflow::create($validated);

        return redirect()
            ->route('mcm.index')
            ->with('status', 'Workflow saved.');
    }
}
