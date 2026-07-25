<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function create()
    {
        return view('campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', 'string', 'max:255'],
            'objective'    => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],

            'channel'      => ['required', 'string', 'max:255'],
            'audience'     => ['required', 'string', 'max:255'],
            'subject_line' => ['required', 'string', 'max:255'],
            'message'      => ['required', 'string', 'max:2000'],
            'media'        => ['nullable', 'file', 'max:10240'],

            'send_date'    => ['required', 'date'],
            'send_time'    => ['nullable'],
            'end_date'     => ['nullable', 'date'],
            'repeat_campaign' => ['nullable', 'string', 'max:255'],
            'send_option'  => ['required', 'in:immediate,schedule'],
            'notes'        => ['nullable', 'string'],
            'timezone'     => ['nullable', 'string', 'max:255'],

            'status'       => ['required', 'in:draft,scheduled'],
        ]);

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

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', 'string', 'max:255'],
            'objective'    => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],

            'channel'      => ['required', 'string', 'max:255'],
            'audience'     => ['required', 'string', 'max:255'],
            'subject_line' => ['required', 'string', 'max:255'],
            'message'      => ['required', 'string', 'max:2000'],
            'media'        => ['nullable', 'file', 'max:10240'],

            'send_date'    => ['required', 'date'],
            'send_time'    => ['nullable'],
            'end_date'     => ['nullable', 'date'],
            'repeat_campaign' => ['nullable', 'string', 'max:255'],
            'send_option'  => ['required', 'in:immediate,schedule'],
            'notes'        => ['nullable', 'string'],
            'timezone'     => ['nullable', 'string', 'max:255'],

            'status'       => ['required', 'in:draft,scheduled'],
        ]);

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