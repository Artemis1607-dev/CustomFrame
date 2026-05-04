<?php

namespace Core;

/**
 * Resolves a request and wires the necessary classes dealing with it.
 * 
 * First of all, Core\Router makes sure a route is existant. Otherwise, the 
 * 404 error is thrown. In the current setup, two route types are available:
 * 
 * * Static routes: classic routes without parameters
 * * Dynamic routes: modern routes with parameters
 * 
 * Once the resolution is finished, Core\Router is checking whether a route
 * has any middlewares to be applied before dispatching. Once again, there's
 * two options:
 * 
 * * In case at least one middleware is present, filter the request
 * * In case no middleware is present, pursue directly with dispatching
 * 
 * Finally, the last step is to apply a specified controller, whether it
 * is defined or anonymous, Core\Router is intended to handle both with or
 * not dynamic parameter passing.
 */
class Router
{
    /**
     * Holds the predefined routes.
     * 
     * @see /config/routes.php
     */
    protected array $routes;

    /**
     * Assigns the predefined routes.
     * 
     * For performance reasons, it is more efficient to group
     * the routes by their method.
     */
    public function __construct() 
    {
        // Sort routes according to their method
        foreach (routes() as $route) {
            $this->routes[$route->method][] = $route;
        }
    }

    /** 
     * Resolves an incoming request.
     * 
     * This method is used to compare an incoming request with the
     * predefined routes.
     * 
     * @throws \RuntimeException
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
        throw new \RuntimeException('Resource not found', 404);
    }
    
    /**
     * Processes the matched route.
     * 
     * This method checks if a middleware is associated to the route, 
     * deciding whether to process() or to dispatch() the current request.
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
     * the request for potentially invalid data. Once finished, it calls 
     * the dispatch method which passes a theoretically safe request to 
     * the associated controller.
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