<?php

namespace Core;

/**
 * Provides an object-oriented request reference.
 * 
 * The purpose of Request is to identify and to provide 
 * all the necessary information on the incoming request.
 */
class Request
{
    /** Holds the request method. */
    public string $method;

    /** Holds the request URL. */
    public string $url;

    /** Holds the request body. */
    public array $body;

    /** Holds the request headers. */
    public array $headers;

    /**
     * Holds the dynamic variables of a request.
     * 
     * This feature enables the user to utilize the dynamic
     * routes. Conceptually, it is intended to avoid overcharging
     * the query or access a specific resource in the OOP context.
     * In practice, in case a dynamic route isdetected, the parameters 
     * are passed to an associated controller as ...$array. To find out
     * more, you may refer to the the resource below:
     * 
     * @see \Core\Router
     */
    public array $dynamic = [];

    /**
     * Transmitted information from middlewares to a controller.
     * 
     * Sometimes middlewares have to supply the controller with
     * additonal information, so as to modify a certain behaviour.
     * This can be done easily by setting custom attributes in
     * middlewares and getting them in controllers.
     */
    public array $attributes = [];
    
    /** Sets the request information. */
    public function __construct()
    {
        $this->method = $this->getMethod();
        $this->url = $this->getUrl();
        $this->headers = $this->getHeaders();
        $this->body = $this->getBody();
    }
    
    /** 
     * Returns a normalized method.
     * 
     * Note that method validation is done with the switch 
     * statement in getBody().
     * 
     * @throws \RuntimeException
     */
    protected function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /** Returns the relative url. */
    protected function getUrl(): string
    {
        // Normalize
        $normalized_url = strtolower(trim($_SERVER['REQUEST_URI'], ' '));
        // Validate
        if ($normalized_url === '') {
            return '/';
        }
        // Sanitize
        $sanitized_url = preg_replace('~[^\w/]+~', '', $normalized_url);
        return preg_replace('~/{2,}~', '/', $sanitized_url);
    }

    /**
     * Returns an array of transmitted parameters.
     * 
     * @throws \RuntimeException
     * @throws \JsonException
     * @todo media type check
     */
    protected function getBody(): array
    {
        switch ($this->method) {
            case 'get':
                parse_str($_SERVER['QUERY_STRING'], $body);
                break;
            case 'post':
            case 'patch':
            case 'put':
            case 'delete':
                // Retrieve raw json
                $json = file_get_contents('php://input');
                // Turn raw json into a decoded associative array
                $body = json_decode($json, true);
                // Handle json-related errors
                if ($body === null) {
                    throw new \JsonException('Invalid request body', 400);
                }
                break;
            default:
                throw new \RuntimeException('Unsupported request method', 500);
        }
        return $body;
    }

    /** 
     * Extracts an array with all the available HTTP headers. 
     *
     * @throws \RuntimeException
     * @link https://www.php.net/manual/en/function.getallheaders.php
     */
    protected function getHeaders(): array
    {
        $headers = [];
        // Tranform HTTP_FOO_BAR to Foo-Bar
        foreach ($_SERVER as $name => $value) 
        {
            if (substr($name, 0, 5) == 'HTTP_') 
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}