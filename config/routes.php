<?php

/**
 * Defines the application's routes.
 * 
 * This array is then required by a helper function 
 * to Router for the validation of the request.
 * 
 * @see /boot/helpers.php
 * @see \Core\Router
 */

use App\Middlewares\AuthMiddleware;
use App\Middlewares\SessionMiddleware;
use Core\Route;
use App\Controllers\{AuthController, HomeController};

return [
    Route::get('/login', [AuthController::class => 'login']),
    Route::post('/login/signin', [AuthController::class => 'signin']),
    Route::post('/login/signup', [AuthController::class => 'signup']),
    Route::patch('/login/reset', [AuthController::class => 'reset']),
    
    Route::get('/home/guest', [HomeController::class => 'guest']),
    Route::get('/home/user', [HomeController::class => 'user'])
    ->middleware(SessionMiddleware::class, AuthMiddleware::class.':user'),
    Route::get('/home/admin', [HomeController::class => 'admin'])
    ->middleware(SessionMiddleware::class, AuthMiddleware::class.':admin'),
    
    // Database & Dynamic route testing
    Route::get('/test/{test}', function($request, $first) {
        // Interaction with View
        return view('home', [
            'dynamic' => $first,
            'title' => 'Dynamic route',
        ]);
    })->middleware(SessionMiddleware::class.':admin'),
];