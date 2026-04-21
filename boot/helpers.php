<?php 

use Core\Response;

/** 
 * Loads an array with the defined routes. 
 */
function routes(): array
{
    return require_once __DIR__ . "/../config/routes.php";
}

/** 
 * Loads an array with the defined database configuration. 
 */
function database(): array
{
    return require_once __DIR__ ."/../config/database.php";
}

/** 
 * Checks whether the provided class and its method are valid.
 */
function validate(string $class, string $method): bool
{
    if (
        !class_exists($class)
        || !method_exists($class, $method)
    ) {
        return false;
    }
    return true;
}

function view(string $view, array $data = [], $extension = false): Response
{
    $extension === false ?: $view .= '.blade.php';
    return Response::view($view, $data);
}