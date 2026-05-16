<?php

use Core\ViewWrapper;
use eftec\bladeone\BladeOne;
use PHPUnit\Framework\TestCase;

/**
 * Testing the following condition:
 * 
 * * Invalid compiler call
 */
class ViewTest extends TestCase
{
    public function testFailOnUnexistantCompilerInstance()
    {
        if (isset(BladeOne::$instance)) {
            $this->markTestSkipped();
        }
        
        $this->expectException(\LogicException::class);

        ViewWrapper::render('foo');
    }
}