<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $page)
    {
        $titles = [
            'reports' => 'Reports',
            'exit' => 'EXIT',
        ];

        abort_unless(array_key_exists($page, $titles), 404);

        return view('placeholder', ['title' => $titles[$page]]);
    }
}
