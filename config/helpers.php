<?php 

/**
 * This file regroups helper function which have to
 * be available accross the whole codebase.
 */

use Core\Response;

/** 
 * Loads an array with the defined routes.
 *
 * @throws \LogicException 
 */
function routes(): array
{
    if (!file_exists(__DIR__ . '/routes.php')) {
        throw new LogicException('Routes not found', 500);
    }
    return require_once __DIR__ . "/routes.php";
}

/** Validates a class and its method. */
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

/** Provides a shortcut for the view response. */
function view(string $view, array $data = []): Response
{
    return Response::prepareView($view, $data);
}

/** Provides a shortcut for the json response. */
function json(array $json): Response 
{
    return Response::prepareJson($json);
}

/** Provides a shortcut for the file response. */
function prepareFile(string $path): Response 
{
    return Response::prepareFile($path);
}

/** Provides a shortcut for the redirect response. */
function redirect(string $url, int $status): Response 
{
    return Response::prepareRedirect($url, $status);
}