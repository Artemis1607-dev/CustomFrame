<?php

namespace Core;

/**
 * Predefines the route structure.
 * 
 * The purpose of Route is to provide a set of predefined routes 
 * that instruct the Router on how to respond to a request.
 * 
 * @see /config/routes.php
 */
class Route
{
    /**
     * Specifies a valid method.
     */
    public string $method;

    /**
     * Holds a valid route.
     */
    public string $url;

    /**
     * Specifies a controller to apply to a route.
     * 
     * @var array|callable 
     *      Accepts a defined, or an anonymous controller as following 
     *      function($request) {...} and return a response.
     */
    public $controller;
    
    /**
     * Holds a controller's method.
     */
    public string $action;
    
    /**
     * Stocks default middlewares applied to routes.
     * 
     * In case manually specified, the middlewares below would be 
     * considered "default". Otherwise, by associating middlewares 
     * directly with the middleware(), these are considered optional 
     * and never dublicate in both cases.
     */
    public array $middlewares = [];

    /**
     * Holds middleware groups which are used to quickly apply 
     * necessary middlewares.
     * 
     * Groups specified below don't directly apply to the routes as
     * with $middlewares. In fact, these should be applied with the
     * group() method. Beforehand, groups have to be defined as nested
     * arrays with specified middlewares.
     */
    protected array $groups = [];
    
    /**
     * Creates a new route with the provided parameters.
     */
    public function __construct(
        string $method,
        string $url,
        string|callable $controller,
        string $action
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     * Creates a route to read data.
     */
    public static function get(string $url, array|callable $controller): self
    {
        return self::setRoute($url, $controller, 'get');
    }

    /**
     * Creates a route to create data.
     */
    public static function post(string $url, array|callable $controller): self
    {
        return self::setRoute($url, $controller, 'post');
    }

    /**
     * Creates a route to update data.
     */
    public static function patch(string $url, array|callable $controller): self
    {
        return self::setRoute($url, $controller, 'patch');
    }

    /**
     * Creates a route to overwrite data.
     */
    public static function put(string $url, array|callable $controller): self
    {
        return self::setRoute($url, $controller, 'put');
    }

    /**
     * Creates a route to delete data.
     */
    public static function delete(string $url, array|callable $controller): self
    {
        return self::setRoute($url, $controller, 'delete');
    }
    
    /**
     * Adds a stackable middleware class to a specified route.
     */
    public function middleware(string ...$middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->setMiddleware($middleware);
        }
        // Pursue the chaining
        return $this;
    }
    
    /**
     * Adds a stackable middleware group to a specified route.
     */
    public function group(string ...$group): self
    {
        foreach ($group as $name) {
            // Check whether the group exists
            if(!isset($this->groups[$name])) {
                throw new \InvalidArgumentException("\"$name\" not found", 500);
            }
            // Apprend associated middlewares to the route
            foreach ($this->groups[$name] as $middleware) {
                $this->setMiddleware($middleware);
            }
        }
        // Pursue the chaining
        return $this;
    }

    /**
     * Helper function which creates a route.
     */
    protected static function setRoute(
        string $url,
        array|callable $controller,
        string $method
    ): self {
        if (is_array($controller)) {
            // Check whether the current class is valid
            if (!validate(key($controller), current($controller))) {
                throw new \InvalidArgumentException(
                    '"'. key($controller) . '->' . current($controller) . '()" not found',
                    500
                );
            }
            // Create a route with a specified controller
            return new self($method, $url, key($controller), current($controller));
        } else {
            // Create a route with an anonymous controller
            return new self($method, $url, $controller, '');
        }
    }

    protected function setMiddleware(string $middleware): void
    {
        // Identify middleware parameters
        if (preg_match('~([\\\\a-zA-Z]+):([\w,]+)~', $middleware, $matches)) {
            $class = $matches['1'];
            $attributes = explode(',', $matches['2']);
            $this->processMiddleware($class, $attributes);
        } else {
            $this->processMiddleware($middleware);
        }
    }

    protected function processMiddleware(string $middleware, array $attributes = []): void
    {
        // Check whether the current class is valid
        if (!validate($middleware, 'filter')) {
            throw new \InvalidArgumentException(
                '"'. $middleware . '->filter()" not found',
                500
            );
        }
        // Prevent duplicates
        if (!isset($this->middlewares[$middleware])) {
            $this->middlewares[$middleware] = $attributes;
        }
    }
}