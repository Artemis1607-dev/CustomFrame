<?php

namespace App\Models;

/**
 * "Data" class in the MVC structure.
 * 
 * The purpose of Models is to execute specific SQL queries according
 * to the CRUD. It includes transferring requested data to the controller.
 */
class Model
{
    public static function foo(): array
    {
        return $foo = [
            'foo' => 'foo',
            'bar' => 'bar'
        ];
    }
}