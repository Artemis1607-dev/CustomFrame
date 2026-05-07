<?php

use KernelTest;
use Core\Request;

/**
 * Testes Core\Request.
 * 
 * * Pursue on valid method
 * * Exception on invalid method
 * * Pursue on valid url
 * * Exception on invalid url
 * * Pursue on valid body
 * * Exception on invalid body
 * * Pursue on valid headers
 * * Exception on invalid headers
 */
class RequestTest extends TestCase
{
    public function testCanBeInitializedFromIValidMethod(): void
    {
        // Arrange
        $this->simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame($request->method, 'get');
    }

    public function testCanNotBeInitializedFromIInvalidMethod(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        $this->simulateRequest('HEAD');
        // Act
        new Request();
    }

    public function testCanBeInitializedFromValidUrl(): void
    {
        // Arrange
        $this->simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame($request->url, '/');
    }

    public function testCanNotBeInitializedFromInvalidUrl(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        $this->simulateRequest('GET', '/invalid/route// kk // **');
        // Act
        new Request();
    }

    public function testCanBeInitializedFromValidBody(): void
    {
        // Arrange
        $this->simulateRequest(
            'PATCH',
            '/json',
            '{"foo":"bar"}',
        );
        // Act
        $request = new Request();
        // Assert
        $this->assertSame($request->body, ['foo' => 'bar']);
    }

    public function testCanNotBeInitializedFromInvalidBody(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        $this->simulateRequest(
            'PATCH',
            '/json',
            '{"foo":\'bar\'}',
        );
        // Act
        new Request();
    }

    public function testCanBeInitializedFromValidHeaders(): void
    {
        // Arrange
        $this->simulateRequest();
        // Act
        $request = new Request();
        // Assert
        $this->assertSame($request->headers, ['HTTP_ACCEPT' => 'text/css']);
    }

    public function testCanNotBeInitializedFromInvalidHeaders(): void
    {
        // Expect
        $this->expectException(\RuntimeException::class);
        // Arrange
        $this->simulateRequest(
            'GET',
            '/',
            ['foo' => 'bar'],
            []
        );
        // Act
        new Request();
    }
}