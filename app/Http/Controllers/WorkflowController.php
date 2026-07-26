<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkflowRequest;
use App\Models\Workflow;

class WorkflowController extends Controller
{
    public function create()
    {
        return view('workflows.create');
    }

    public function store(WorkflowRequest $request)
    {
        Workflow::create($request->validated());

        return redirect()
            ->route('mcm.index')
            ->with('status', 'Workflow saved.');
    }
}