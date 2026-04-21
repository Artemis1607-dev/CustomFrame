<?php

namespace App\Models;

use Core\ModelWrapper;
use Core\Request;

/**
 * "Data" class in the MVC structure.
 * 
 * The purpose of Models is to execute specific SQL queries according
 * to the CRUD. It includes transferring requested data to the controller.
 */
class AuthModel
{
    public static function readCredentials(string $email, string $password): ?array
    {
        $query = "SELECT id, role FROM account WHERE email=? AND password=?";
        $result = ModelWrapper::$instance->execute_query($query, [$email, $password]);
        return $result ? $result->fetch_assoc() : null;
    }
}