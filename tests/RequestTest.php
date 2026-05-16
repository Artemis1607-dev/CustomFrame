<?php

use Core\Request;
use PHPUnit\Framework\TestCase;

/**
 * Testes the following conditions:
 * 
 * * Valid, Invalid, Empty method
 * * Valid, Invalid, Empty URL
 * * Valid, Invalid, Empty headers
 * * Valid, Invalid, Empty body
 */
final  class RequestTest extends TestCase
{
    public function testPursueOnValidRequest()
    {
        // Expect
        self::simulateRequest('GET', '/', 'foo=bar', [
            'HTTP_ACCEPT' => 'text/css'
        ]);
        // Arrange
        $request = new Request();
        // Act
        $this->assertSame('get', $request->method);
        $this->assertSame('/', $request->url);
        $this->assertSame(['foo' => 'bar'], $request->body);
        $this->assertSame(['Accept' => 'text/css'], $request->headers);
    }

    public function testFailOnInvalidMethod(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        self::simulateRequest('HEAD');
        // Act
        new Request();
    }

    public function testFailOnEmptyMethod(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        self::simulateRequest('');
        // Act
        new Request();
    }

    public function testPursueOnInvalidUrl(): void
    {
        // Arrange
        self::simulateRequest('GET', 'foo/ ?? == // bar');
        // Act
        $request = new Request();
        // Assert
        $this->assertSame('foo/bar', $request->url);
    }

    public function testPursueOnEmptyUrl(): void
    {
        // Arrange
        self::simulateRequest('GET', '');
        // Act
        $request = new Request();
        // Assert
        $this->assertSame('/', $request->url);
    }

    public function testPursueOnInvalidGetBody(): void
    {
        // Arrange
        self::simulateRequest('GET', '/', '=bar');
        // Act
        $request = new Request();
        // Assert
        $this->assertEmpty($request->body);
    }

    public function testPursueOnInvalidHeaders(): void
    {
        // Arrange
        self::simulateRequest('GET', '/', 'foo=bar', ['FOO_BAR_HTTP' => 'BAZ']);
        // Act
        $request = new Request();
        // Assert
        $this->assertEmpty($request->headers);
    }

    /** Simulates a request for testing purposes. */
    public static function simulateRequest(
        string $method = 'GET',
        string $url = '/',
        string $body = '',
        array $headers = []
    ): void {
        $_SERVER = $headers;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SERVER_NAME'] = 'www.foo.com';
        $_SERVER['QUERY_STRING'] = $body;
    }
}