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

function view(
    string $view,
    array $data = []
): Response {
    return Response::prepareView($view, $data);
}

function json(array $json): Response 
{
    return Response::prepareJson($json);
}

function fileResponse(string $path): Response 
{
    return Response::prepareFile($path);
}

function redirect(string $url): Response 
{
    return Response::redirect($url);
}
