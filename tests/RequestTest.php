<?php

use Core\Request;
use PHPUnit\Framework\TestCase;

/**
 * Testes Core\Request.
 * 
 * * Pursue on valid method
 * * Exception on invalid method
 * * Pursue on valid url
 * * Exception on invalid url
 * * Pursue on valid body
 * * Pursue on invalid body
 * * Pursue on valid headers
 * * Pursue on invalid headers
 */
final  class RequestTest extends TestCase
{
    public function testCanBeInitializedFromValidMethod(): void
    {
        // Arrange
        simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame('get', $request->method);
    }

    public function testCanNotBeInitializedFromInvalidMethod(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        simulateRequest('HEAD');
        // Act
        new Request();
    }

    public function testCanBeInitializedFromValidUrl(): void
    {
        // Arrange
        simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame('/', $request->url);
    }

    public function testCanBeInitializedFromValidBody(): void
    {
        // Arrange
        simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame(['foo' => 'bar'], $request->body);
    }

    public function testCanBeInitializedFromEmptyBody(): void
    {
        // Arrange
        simulateRequest('GET', '/', '');
        // Act
        $request = new Request();
        // Assert
        $this->assertEmpty($request->body);
    }

    public function testCanBeInitializedFromValidHeaders(): void
    {
        // Arrange
        simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame(['Accept' => 'text/css'], $request->headers);
    }

    public function testCanBeInitializedFromEmptyHeaders(): void
    {
        // Arrange
        simulateRequest('GET', '/', 'foo=bar', []);
        // Act
        $request = new Request();
        // Assert
        $this->assertEmpty($request->headers);
    }
}