<?php

use Core\Kernel;
use PHPUnit\Framework\TestCase;

/** 
 * Testes Core\Kernel.
 * 
 * * Output is provided on request handling
 * * Exception on invalid config
 */
final class KernelTest extends TestCase
{
    public function testThrowExceptionOverInvalidConfiguration(): void
    {
        // Expect
        $this->expectException(\LogicException::class);
        // Act
        new Kernel([])->handleRequest();
    }
}