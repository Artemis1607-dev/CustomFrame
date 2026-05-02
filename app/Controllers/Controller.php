<?php

namespace App\Controllers;

use App\Models\Model;
use Core\{Response, Request};

/** Simulates a mockup controller. */
class Controller 
{
    /**
     * @param Request $request
     *        Passed as request from \Core\Router.
     * @return \Core\Response
     */
    public function foo(Request $request): Response
    {
        $foo = Model::foo();
        return view('foo', ['foo' => $foo]);
    }
}