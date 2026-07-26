<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('customer')->orderBy('id')->get();
        return view('support.index', compact('tickets'));
    }
}