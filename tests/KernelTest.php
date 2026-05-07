<?php

use Core\Kernel;

/** 
 * Testes Core\Kernel.
 * 
 * * Output is provided on request handling
 * * Exception on invalid config
 */
final class KernelTest extends TestWrapper
{
    public function testOutputIfManagedToHandleRequest(): void
    {
        // Arrange
        $this->simulateRequest();
        // Act
        new Kernel($this->config)->handleRequest();
        $response_body = ob_get_contents();
        // Assert
        $this->assertString($response_body);
    }
    
    public function testOutputIfFailedToHandleRequest(): void
    {
        // Arrange
        $this->simulateRequest('POST');
        // Act
        new Kernel($this->config)->handleRequest();
        $response_body = ob_get_contents();
        // Assert
        $this->assertString($response_body);
    }
    
    public function testThrowExceptionOverInvalidConfiguration(): void
    {
        // Expect
        $this->expectException(\InvalidArgumentException::class);
        // Act
        new Kernel([])->handleRequest();
    }
}