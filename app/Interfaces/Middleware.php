<?php

namespace App\Interfaces;

use Core\Request;
use Closure;

/**
 * Filters an incoming request.
 * 
 * The purpose of a middleware is to filter an incoming request ensuring 
 * a secure flow. Since Router supports the class chaining, those can be 
 * stacked into multiple security layers.
 */
interface Middleware
{
    /**
     * This method is used to filter a request. 
     * 
     * @return object Closure
     */
    public function filter();
}