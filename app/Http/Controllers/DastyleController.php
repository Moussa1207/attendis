<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DastyleController extends Controller
{
    //
    public function register(){
        return view('Dashboard.register');
    }
    public function indexD(){
        return view('Dashboard.index');
    }
    public function auth500(){
        return view('Dashboard.auth-500');
    }
    public function auth404(){
        return view('Dashboard.auth-404');
    }
    public function authlock(){
        return view('Dashboard.auth-lock-screen');
    }
    public function authlogin(){
        return view('Dashboard.auth-login');
    }
    public function authreverpw(){
        return view('Dashboard.auth-rever-pw');
    }
    public function widgetsD(){
        return view('Dashboard.widgets');
    }

    public function uivideo(){
        return view('Dashboard.uivideos');
    }
    public function appschat(){
        return view('Dashboard.apps-chat');
    }
    public function appscalendar(){
        return view('Dashboard.apps-calendar');
    }
    public function appscontact(){
        return view('Dashboard.apps-contact');
    }
}
