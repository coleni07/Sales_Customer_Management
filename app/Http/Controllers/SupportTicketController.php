<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::orderBy('id')->get();

        return view('support.index', compact('tickets'));
    }
}
