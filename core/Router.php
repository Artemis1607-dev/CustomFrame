<?php

namespace Core;

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
    public function resolveRoute(Request $request): Response
    {
        // Make sure the requested route exists
        foreach ($this->routes[$request->method] as $route) {
            // Compare the request and the static route
            if (
                $route->method === $request->method
                && $route->url === $request->url
            ) {
                return $this->processRoute($request, $route);
            } else {
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
                        return $this->processRoute($request, $route);
                    }
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
    protected function processRoute(
        Request $request,
        Route $route,
    ): Response {
        // Check for a middleware
        if (!empty($route->middlewares)) {
            return $this->handleMiddleware($request, $route);
        }
        return $this->dispatchRoute($request, $route);
    }

    /**
     * Filters an incoming request.
     * 
     * This method applies a chain of middleware classes so as to filter
     * the request for potentially harmful infomation. Once finished,
     * it calls the dispatch method which passes a theoretically safe
     * request to the associated controller.
     */
    protected function handleMiddleware(
        Request $request,
        Route $route
    ): Response {
        // Define the final closure
        $next = function(Request $request) use ($route): Response {
            return $this->dispatchRoute($request, $route);
        };
        // Define the Middleware chain
        foreach (array_reverse($route->middlewares) as $middleware => $parameters) {
            // Define the chain
            $Middleware = new $middleware();
            $next = function(Request $request) use ($Middleware, $next, $parameters) {
                return $Middleware->filter($request, $next, ...$parameters);
            };
        }
        // Call the chain and the final closure
        return $next($request);
    }
    
    /**
     * Passes an incoming request to the associated controller.
     * 
     * Additionally, this method checks whether currect route has a valid 
     * controller and its method. The output is then sent to the initial 
     * caller, in this case Kernel.
     */
    protected function dispatchRoute(
        Request $request,
        Route $route
    ): Response {
        // Use a defined controller
        if (is_string($route->controller)) {
            // Render the corresponding view
            $controller = new ($route->controller)();
            $action = $route->action;
            return $controller->$action($request, ...$request->dynamic);
        }
        // Use anonymous controller
        return ($route->controller)($request, ...$request->dynamic);
    }
}