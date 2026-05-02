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
     * @see Core\Router
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
        $this->method = self::getMethod();
        $this->url = self::getUrl();
        $this->body = self::getBody();
        $this->headers = self::getHeaders();
    }
    
    /** 
     * Returns a normalized method.
     * 
     * @throws \RuntimeException
     */
    protected static function getMethod(): string
    {
        // Retreive and normalize
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (empty($method)) {
            throw new \RuntimeException('Invalid request method', 400);
        }
        return $method;
    }

    /**
     * Returns the relative url.
     * 
     * @throws \RuntimeException
     */
    protected static function getUrl(): string
    {
        // Retrieve
        $raw_url = (($_ENV['PRODUCTION'] ?? true) === false
            ? 'http://'
            : 'https://')
            . $_SERVER['SERVER_NAME']
            . $_SERVER['REQUEST_URI'];
        // Normalize
        $normalized_url = strtolower(trim($raw_url, ' /'));
        // Validate (checks the whole url)
        if (!filter_var(
            $raw_url,
            FILTER_VALIDATE_URL,
            FILTER_FLAG_PATH_REQUIRED
        )) {
            throw new \RuntimeException('Invalid request URL', 400);
        }
        // Sanitize (allow only single slashes, a-z, A-Z and 0-9)
        $parsed_url = parse_url($normalized_url, PHP_URL_PATH);
        $url = preg_replace('~[^\w/]+~', '', $parsed_url);
        if ($url === '') {
            return $url = '/';
        }
        return preg_replace('~/{2,}~', '/', $url);
    }

    /**
     * Returns an array of transmitted parameters.
     * 
     * @throws \RuntimeException
     */
    protected static function getBody(): array
    {
        switch (self::getMethod()) {
            case 'get':
                $body = $_GET;
                break;
            case 'post':
                $body = $_POST;
                break;
            case 'patch':
            case 'put':
            case 'delete':
                // Retrieve raw json
                $json = file_get_contents('php://input');
                // Handle json-related errors
                if ($json === null) {
                    throw new \JsonException('Invalid request body', 400);
                }
                // Turn raw json into a decoded associative array
                $body = json_decode($json, true);
                break;
            default:
                throw new \RuntimeException('Unsupported request method', 500);
        }
        return !empty($body) ? $body : [];
    }

    /** Extracts an array with all the available HTTP headers. */
    protected static function getHeaders(): array
    {
        return getallheaders();
    }
}