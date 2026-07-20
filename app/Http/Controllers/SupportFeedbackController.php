<?php

namespace App\Http\Controllers;

use App\Models\SupportFeedback;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportFeedbackController extends Controller
{
    public function create(SupportTicket $ticket)
    {
        return view('support.feedback', compact('ticket'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        SupportFeedback::create($data);

        return redirect()
            ->route('support.index')
            ->with('success', 'Feedback submitted successfully!');
    }
}
