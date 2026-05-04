<?php

/**
 * This file holds the application's routes.
 * 
 * Note that the following array is then required by
 * Core\Router for the request URL validation. Be free
 * to check Core\Route to find out on the available
 * route features.
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