<?php

use App\Controllers\Controller;
use App\Middlewares\Middleware;
use Core\Request;
use Core\Route;
use Core\Router;
use PHPUnit\Framework\TestCase;

/**
 * Testing the following conditions:
 * 
 * * Invalid, Empty config
 * * Valid/Invalid {Static, Dynamic} {Defined, Anonymous} [Filtered] route
 */
class RouterTest extends TestCase
{
    public function testFailOnResourceNotFound()
    {
        $this->expectException(\RuntimeException::class);

        KernelTest::loadDependencies();
        RequestTest::simulateRequest('HEAD','/invalid');
        $request = new Request();
        $router = new Router([
            Route::get('/foo', function(Request $request) {
                return view('foo');
            })
        ]);

        $router->resolveRoute($request);
    }

    public function testFailOnInvalidConfig()
    {
        $this->expectException(\LogicException::class);

        new Router(['/foo']);
    }

    public function testFailOnEmptyConfig()
    {
        $this->expectException(\LogicException::class);

        new Router([]);
    }

    public function testPursueOnStaticDefinedRoute()
    {
        KernelTest::loadDependencies();
        RequestTest::simulateRequest('GET', '/foo');
        $request = new Request;
        $router = new Router([
            Route::get('/foo', [Controller::class => 'foo'])
        ]);

        $router->resolveRoute($request);

        $this->assertIsObject($router->controller);
        $this->assertSame($router->action, 'foo');
    }

    public function testPursueOnStaticAnonymousRoute()
    {
        KernelTest::loadDependencies();
        RequestTest::simulateRequest('GET', '/foo');
        $request = new Request;
        $router = new Router([
            Route::get('/foo', function(Request $request) {
                return view('foo');
            })
        ]);

        $router->resolveRoute($request);

        $this->assertIsCallable($router->controller);
    }

    public function testPursueOnDynamicDefinedRoute()
    {
        KernelTest::loadDependencies();
        RequestTest::simulateRequest('GET', '/foo/bar');
        $request = new Request;
        $router = new Router([
            Route::get('/foo/{bar}', [Controller::class => 'foo'])
        ]);

        $router->resolveRoute($request);

        $this->assertIsObject($router->controller);
        $this->assertSame($router->action, 'foo');
        $this->assertSame($request->dynamic, [0 => 'bar']);
    }

    public function testPursueOnDynamicAnonymousRoute()
    {
        KernelTest::loadDependencies();
        RequestTest::simulateRequest('GET', '/foo/baz');
        $request = new Request;
        $router = new Router([
            Route::get('/foo/{baz}', function(Request $request) {
                return view('foo');
            })
        ]);

        $router->resolveRoute($request);

        $this->assertIsCallable($router->controller);
        $this->assertSame($request->dynamic, [0 => 'baz']);
    }

    public function testPursueOnAnyFilteredRoute()
    {
        KernelTest::loadDependencies();
        RequestTest::simulateRequest('GET', '/foo');
        $request = new Request;
        $router = new Router([
            Route::get('/foo', [Controller::class => 'foo'])->middleware(Middleware::class)
        ]);

        $router->resolveRoute($request);

        $this->assertIsObject($router->controller);
        $this->assertSame($router->action, 'foo');
        $this->assertSame($request->attributes, ['foo' => 'bar']);
    }
}