<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Theme;

class DashboardController extends Controller
{
    public function index()
    {

        $data['posts'] = "Crazy";
        // Theme::uses('demoone');
        // return Theme::view('dashboard.panel', $data);
        return view('dashboard.panel', $data);

    }
}
