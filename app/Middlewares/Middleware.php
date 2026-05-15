<?php

namespace App\Middlewares;

use Core\Request;

/** Simulates a mockup Middleware */
class Middleware
{
    public function filter(Request $request, \Closure $next)
    {
        $request->attributes['foo'] = 'bar';
        return $next($request);
    }
}