<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function adminDashboard()
    {  
        return view('dashboard_layout.index');
    }
    
    public function userDashboard()
    {
        return view('dashboard_layout.index');
    }
}