<?php

namespace Core;

use Throwable;

/**
 * Validates a request and wires the necessary classes dealing with it.
 * 
 * The purpose of Router is to decide whether a request is valid
 * or invalid by comparing an incoming request to the existing routes.
 */
class Router
{
    /**
     * Holds the predefined routes.
     * 
     * @see /config/routes.php
     */
    public array $routes;

    /**
     * Assigns an array with the routes to the $routes property.
     * 
     * In order to improve perfomance, it is more convinient to group
     * the routes by their method specified in the instance's property.
     * 
     * @see \Core\Route
     */
    public function __construct() 
    {
        // Sort routes according to their method
        foreach (routes() as $route) {
            $this->routes[$route->method][] = $route;
        }
    }

    /** 
     * Analyzes an incoming request.
     * 
     * This method is used to compare an incoming request and already
     * defined static or dynamic routes.
     */
    public function resolve(Request $request): Response
    {
        // Make sure the requested route exists
        foreach ($this->routes[$request->method] as $route) {
            // Identify the route type
            if (preg_match('~\{[^/]+\}~', $route->url)) {
                $regular_url = preg_replace(
                    '~\{[^/]+\}~',
                    '([^/]+)',
                    $route->url
                );
                $regular_pattern = "~^$regular_url$~";
                // Compare the request and the dynamic route
                if (
                    preg_match($regular_pattern, $request->url, $matches)
                ) {
                    array_shift($matches);
                    $request->dynamic = $matches;
                    return self::process($request, $route);
                }
            } else {
                // Compare the request and the static route
                if (
                    $route->method === $request->method
                    && $route->url === $request->url
                ) {
                    return self::process($request, $route);
                }
            }
        }
        // In case requested url doesn't exist
        throw new \RuntimeException('Not found', 404);
    }
    
    /**
     * Processes an incoming request.
     * 
     * This method checks if a middleware is associated to the route, 
     * deciding whether to process() or to dispatch() current request.
     */
    protected static function process(
        Request $request,
        Route $route
    ): Response {
        // Check for a middleware
        if (!empty($route->middlewares)) {
            return self::filter($request, $route);
        }
        // In case no middleware is defined
        if (is_string($route->controller)) {
            // Use a defined controller
            return self::dispatch(
                $request,
                $route->controller,
                $route->action
            );
        }
        // Use anonymous controller
        return ($route->controller)($request);
    }

    /**
     * Filters an incoming request.
     * 
     * This method applies a chain of middleware classes so as to filter
     * the request for potentially harmful infomation. Once finished,
     * it calls the dispatch method which passes a theoretically safe
     * request to the associated controller.
     */
    protected static function filter(
        Request $request,
        Route $route
    ): Response {
        // Define the Final Closure
        $next = function($request) use ($route): Response {
            if (is_string($route->controller)) {
                // Use a defined controller
                return self::dispatch(
                    $request,
                    $route->controller,
                    $route->action
                );
            }
            // Use an anonymous controller
            return ($route->controller)($request, ...$request->dynamic);
        };
        // Define the Middleware chain
        foreach (array_reverse($route->middlewares, true) as $middleware => $parameters) {
            // Define the chain
            $Middleware = new $middleware;
            $next = function($request) use ($Middleware, $next, $parameters) {
                return $Middleware->filter($request, $next, ...$parameters);
            };
        }
        // Call the Final closure
        return $next($request);
    }
    
    /**
     * Passes an incoming request to the associated controller.
     * 
     * Additionally, this method checks whether currect route has a valid 
     * controller and its method. The output is then sent to the initial 
     * caller, in this case Kernel.
     */
    protected static function dispatch(
        Request $request,
        string $controller,
        string $action
    ): Response {
        // Render the corresponding view
        $controller = new $controller;
        return $controller->$action($request, ...$request->dynamic);
    }
}