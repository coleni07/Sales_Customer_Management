<?php

namespace App\Http\Controllers;

use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;

class CampaignController extends Controller
{
    public function create()
    {
        return view('campaigns.create');
    }

    public function store(CampaignRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('media')) {
            $validated['media_path'] = $request->file('media')->store('campaign-media', 'public');
        }
        unset($validated['media']);

        Campaign::create($validated);

        return redirect()
            ->route('mcm.index')
            ->with('status', 'Campaign saved.');
    }

    public function edit(Campaign $campaign)
    {
        return view('campaigns.create', compact('campaign'));
    }

    public function update(CampaignRequest $request, Campaign $campaign)
    {
        $validated = $request->validated();

        if ($request->hasFile('media')) {
            $validated['media_path'] = $request->file('media')->store('campaign-media', 'public');
        }
        unset($validated['media']);

        $campaign->update($validated);

        return redirect()
            ->route('mcm.index')
            ->with('status', 'Campaign updated.');
    }
}