<?php

namespace Core;

/**
 * Ensures a proper negociation between the client and the server.
 * 
 * The purpose of Response is to ensure a proper communication 
 * between the server and the client, using provided instructions 
 * from the controller.
 * 
 * @link https://www.iana.org/assignments/media-types/media-types.xhtml
 *       Possible media-types to integrate
 */
class Response
{
    /** Holds the response body. */
    protected string $body;

    /** Holds the response status. */
    protected int $status;

    /** Holds the response headers. */
    protected array $headers = [];

    /** Holds the response cookies. */
    protected array $cookies = [];
    
    /**
     * Prepares a response using the body and the status.
     *  
     * @throws \OutOfRangeException
     */
    public function __construct(string $body, int $status)
    {
        if ($status < 100 || $status > 599) {
            throw new \OutOfRangeException("Invalid status code", 500);
        }
        $this->body = $body;
        $this->status = $status;
    }

    /**
     * Prepares a view response with ViewWrapper.
     * 
     * @param string $view
     *        Accepts a relative path to a blade file.
     * @param array $data
     *        Accepts an associative array with the blade
     *        parameters to integrate into the view.
     * @throws \OutOfRangeException
     */
    public static function prepareView(
        string $view,
        array $data = [],
        int $status = 200
    ): self {
        if ($status >= 300 && $status <= 399 || $status > 499) {
            throw new \OutOfRangeException("Invalid status code", 500);
        }
        $response = new self(ViewWrapper::render($view, $data), $status);
        return $response
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setHeader('Content-Length', strlen($response->body));
    }

    /**
     * Prepares a json response to the client.
     * 
     * @param array $json_decoded
     *        Acceptes a decoded associative json array.
     * @throws \JsonException
     * @throws \OutOfRangeException
     */
    public static function prepareJson(
        array $json_decoded,
        int $status = 100
    ): self {
        if ($status >= 300 && $status <= 399 || $status > 499) {
            throw new \OutOfRangeException("Invalid status code", 500);
        }

        $json_encoded = json_encode($json_decoded);
        if ($json_encoded === false) {
            throw new \JsonException('Invalid response body', 500);
        }

        $response = new self(json_encode($json_encoded), $status);
        return $response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Length', strlen($response->body));
    }

    /**
     * Prepares a file response to the client.
     * 
     * @param string $relative_path
     *        Accepts a relative path pointing to an existant file.
     * @throws \RuntimeException
     * @throws \OutOfRangeException
     */
    public static function prepareFile(
        string $relative_path,
        int $status = 100
    ): self {
        if ($status >= 300 && $status <= 399 || $status > 499) {
            throw new \OutOfRangeException("Invalid status code", 500);
        }
        if (!file_exists($relative_path)) {
            throw new \RuntimeException('File not found', 404);
        }
        $response = new self(file_get_contents($relative_path), $status);
        return $response
            ->setHeader('Content-Type', mime_content_type($relative_path))
            ->setHeader('Content-Length', filesize($relative_path));
    }

    /**
     * Prepares a redirect response to the client.
     * 
     * @param string $url
     *        Accepts a relative redirect URL path.
     * @param int $status
     *        Accepts redirect statuses from 300 to 399.
     * @throws \OutOfRangeException
     */
    public static function prepareRedirect(string $url, int $status): self
    {
        if ($status < 300 || $status > 399) {
            throw new \OutOfRangeException("Invalid status code", 500);
        }
        $response = new self('', $status);
        return $response->setHeader('Location', $url);
    }
    
    /** 
     * Prepares a response cookie.
     * 
     * @param int $expires 
     *        Specified in hours.
     */
    public function setCookie(
        string $key,
        string $value,
        int $expires = 24
    ): self {
        $this->cookies[$key] = [
            'value' => $value,
            'expires' => time() + $expires * 3600,
        ];
        return $this;
    }
    
    /** Prepares a response header. */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /** 
     * Sends a response to the client.
     *
     * @param bool $stream
     *        ToDo: in case set to true, enable
     *        post-request exchange.
     */
    public function sendResponse(bool $stream = false): void
    {
        // Specified status
        http_response_code($this->status);
        // Defined headers
        if(!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
        // Defined cookies
        if(!empty($this->cookies)) {
            foreach ($this->cookies as $name => $cookie) {
                setcookie($name, $cookie['value'],[
                    'expires' => $cookie['expires'],
                    'path' => '/',
                    'secure' => $_ENV['PRODUCTION'] ?? true,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }
        // Specified body
        echo $this->body;
        // Stops script in case post-response is required
        $stream ?: exit;
    }
}