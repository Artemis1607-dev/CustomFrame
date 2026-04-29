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
    protected string $body;
    protected int $status;
    protected array $headers = [];
    protected array $cookies = [];
    
    /**
     * Prepares a response using the view and the status.
     * 
     * Additionally, this method can directly be used in a controller,
     * enabling sending custom responses. For instance, once an AJAX
     * text content is requested, a controller has to specify
     * new Response($content, $status) and send necessary headers or cookies.
     */
    public function __construct(string $body, int $status)
    {
        $this->body = $body;
        $this->status = $status;
    }

    /**
     * Prepares a view with ViewWrapper.
     * 
     * @param string $view Accepts the name of a blade page
     */
    public static function prepareView(
        string $view,
        array $data = [],
        int $status = 200
    ): self {
        $response = new self(ViewWrapper::render($view, $data), $status);
        return $response
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setHeader('Content-Length', strlen($response->body));
    }

    /**
     * Prepares a json array to the client.
     * 
     * @param array $json_decoded
     */
    public static function prepareJson(
        array $json_decoded,
        int $status = 100
    ): self {
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
     * Prepares a file to the client.
     * 
     * @param string $relative_path
     */
    public static function prepareFile(
        string $relative_path,
        int $status = 100
    ): self {
        if (!file_exists($relative_path)) {
            throw new \RuntimeException('File not found', 500);
        }
        $response = new self(file_get_contents($relative_path), $status);
        return $response
            ->setHeader('Content-Type', mime_content_type($relative_path))
            ->setHeader('Content-Length', filesize($relative_path));
    }

    /**
     * Prepares a redirect response to the client.
     */
    public static function redirect(string $url, int $status): self
    {
        $response = new self('', $status);
        return $response->setHeader('Location', $url);
    }
    
    /** 
     * Prepares a Cookie to a response.
     * 
     * @param int $expires Specified in hours
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
    
    /**
     * Prepares a header to a response.
     */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /** 
     * Sends a response to the client.
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