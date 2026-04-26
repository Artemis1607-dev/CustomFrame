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

use App\Controllers\Controller;
use Core\Request;
use Core\Route;

return [
    Route::get('/', [Controller::class => 'foo']),
    Route::get('/{dynamic}', function(Request $request, string $dynamic) {
        return view('foo', ['foo' => $dynamic]);
    })
];