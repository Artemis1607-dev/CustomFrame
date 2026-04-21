<?php

namespace App\Controllers;

class HomeController {
    public function guest($request)
    {
        return view('home');
    } 

    public function user($request)
    {
        return view('home', [
            'role' => $_SESSION['role'],
            'auth' => $request->attributes['auth']
        ]);
    }

    public function admin($request)
    {
        return view('home', ['role' => $_SESSION['role']]);
    }
}