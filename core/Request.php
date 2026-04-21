<?php

namespace Core;

/**
 * Provides information on an incoming request.
 * 
 * The purpose of Request is to identify and to provide 
 * all the necessary information on an incoming request.
 */
class Request
{
    /**
     * Holds the method of a request.
     */
    public string $method;

    /**
     * Holds the url of a request.
     */
    public string $url;

    /**
     * Holds the body of a request.
     */
    public array $body;

    /**
     * Holds the headers of a request.
     */
    public array $headers;

    /**
     * Holds the dynamic variables of a request.
     * 
     * @see \Core\Router->resolve()
     */
    public array $dynamic = [];

    /**
     * Transmitted information from middlewares to a controller.
     */
    public array $attributes = [];
    
    /**
     * Extracts the information of a request 
     * and assigns it to the properties.
     */
    public function __construct()
    {
        $this->method = self::getMethod();
        $this->url = self::getUrl();
        $this->body = self::getBody();
        $this->headers = self::getHeaders();
    }
    
    /**
     * Returns a normalized method.
     */
    protected static function getMethod(): string
    {
        // Retreive and normalize
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (!empty($method)) {
            return $method;
        }
        throw new \RuntimeException('Method not found', 400);
    }

    /**
     * Returns the relative url according to
     * the PHP_URL_PATH specification.
     * 
     * @link https://www.php.net/manual/fr/function.parse-url.php
     */
    protected static function getUrl(): string
    {
        // Retrieve
        $raw_url = (empty($_SERVER['HTTPS'])
            ? 'http://'
            : 'https://')
            . $_SERVER['SERVER_NAME']
            . $_SERVER['REQUEST_URI'];
        // Validate
        if (filter_var(
            $raw_url,
            FILTER_VALIDATE_URL,
            FILTER_FLAG_PATH_REQUIRED
        )) {
            // Normalize
            $normalized_url = strtolower(trim($raw_url, '/')); 
            // Sanitize
            $parsed_url = parse_url($normalized_url, PHP_URL_PATH);
            $url = preg_replace('~[^\w/]+~', '', $parsed_url);
            if ($url === '') {
                return $url = '/';
            }
            return preg_replace('~/{2,}~', '/', $url);
        }
        throw new \RuntimeException('URL not valid', 400);
    }

    /**
     * Returns an array of transmitted parameters.
     * 
     * According to the documentation, query parameters are
     * from now accessible with the $_GET superglobal.
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
                $body = json_decode(file_get_contents('php://input'), true);
                break;
            default:
                throw new \RuntimeException('Method not supported', 400);
        }
        return $body ?? [];
    }

    /**
     * Extracts an array with all the available HTTP headers.
     */
    protected static function getHeaders(): array
    {
        return getallheaders();
    }
}