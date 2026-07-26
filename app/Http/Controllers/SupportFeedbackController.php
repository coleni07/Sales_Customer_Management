<?php

namespace App\Http\Controllers;

use App\Models\SupportFeedback;
use App\Models\Ticket;
use Illuminate\Http\Request;

class SupportFeedbackController extends Controller
{
    public function create(Ticket $ticket)
    {
        return view('support.feedback', compact('ticket'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'required|exists:tickets,id', // Changed table reference
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        SupportFeedback::create($data);

        return redirect()
            ->route('support.index')
            ->with('success', 'Feedback submitted successfully!');
    }
}