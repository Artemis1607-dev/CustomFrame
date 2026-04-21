<?php

namespace App\Controllers;

use App\Models\AuthModel;
use Core\Request;
use Core\Response;
use Core\SessionWrapper;

class AuthController extends SessionWrapper
{

    public function login(Request $request)
    {
        return view('login');
    }

    public function signin(Request $request)
    {
        // Validate, normalize and sanitize skipped
        $credentials = AuthModel::readCredentials($request->body['email'], $request->body['password']);
        // Authorize
        if ($credentials === null) {
            return view('login', ['error' => 'Invalid credentials']);
        }
        // Start a new session
        $this->authenticateSession($credentials['id'], $credentials['role']);
        return Response::redirect('/home/' . $credentials['role']);
    }

    public function signup(Request $request)
    {
        //
    }

    public function reset(Request $request)
    {
        //
    }
}