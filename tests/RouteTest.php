<?php

use App\Controllers\Controller;
use App\Middlewares\Middleware;
use Core\Request;
use Core\Route;
use PHPUnit\Framework\TestCase;

/**
 * Testing the following conditions:
 * 
 * * Valid {GET, POST, PATCH, PUT, DELETE} {Defined, Anonymous} [Filtered] route
 * * Invalid group
 * * Invalid middleware
 * * Invalid controller
 * * Invalid parameters
 */
class RouteTest extends TestCase
{
    public function testPursueOnGetDefinedRoute()
    {
        $route = Route::get('/foo', [Controller::class => 'foo']);
    
        $this->assertSame($route->controller, Controller::class);
        $this->assertSame($route->url, '/foo');
        $this->assertSame($route->action, 'foo');
        $this->assertSame($route->method, 'get');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnPostDefinedRoute()
    {
        $route = Route::post('/foo', [Controller::class => 'foo']);
    
        $this->assertSame($route->controller, Controller::class);
        $this->assertSame($route->url, '/foo');
        $this->assertSame($route->action, 'foo');
        $this->assertSame($route->method, 'post');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnDefinedPatchRoute()
    {
        $route = Route::patch('/foo', [Controller::class => 'foo']);
    
        $this->assertSame($route->controller, Controller::class);
        $this->assertSame($route->url, '/foo');
        $this->assertSame($route->action, 'foo');
        $this->assertSame($route->method, 'patch');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnPutDefinedRoute()
    {
        $route = Route::put('/foo', [Controller::class => 'foo']);
    
        $this->assertSame($route->controller, Controller::class);
        $this->assertSame($route->url, '/foo');
        $this->assertSame($route->action, 'foo');
        $this->assertSame($route->method, 'put');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnDeleteDefinedRoute()
    {
        $route = Route::delete('/foo', [Controller::class => 'foo']);
    
        $this->assertSame($route->controller, Controller::class);
        $this->assertSame($route->url, '/foo');
        $this->assertSame($route->action, 'foo');
        $this->assertSame($route->method, 'delete');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnGetAnonymousRoute()
    {
        $route = Route::get('/foo', function(Request $request) {
            return view('foo');
        });
    
        $this->assertIsCallable($route->controller);
        $this->assertSame($route->url, '/foo');
        $this->assertNull($route->action);
        $this->assertSame($route->method, 'get');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnPostAnonymousRoute()
    {
        $route = Route::post('/foo', function(Request $request) {
            return view('foo');
        });
    
        $this->assertIsCallable($route->controller);
        $this->assertSame($route->url, '/foo');
        $this->assertNull($route->action);
        $this->assertSame($route->method, 'post');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnPatchAnonymousRoute()
    {
        $route = Route::patch('/foo', function(Request $request) {
            return view('foo');
        });
    
        $this->assertIsCallable($route->controller);
        $this->assertSame($route->url, '/foo');
        $this->assertNull($route->action);
        $this->assertSame($route->method, 'patch');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnPutAnonymousRoute()
    {
        $route = Route::put('/foo', function(Request $request) {
            return view('foo');
        });
    
        $this->assertIsCallable($route->controller);
        $this->assertSame($route->url, '/foo');
        $this->assertNull($route->action);
        $this->assertSame($route->method, 'put');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnDeleteAnonymousRoute()
    {
        $route = Route::delete('/foo', function(Request $request) {
            return view('foo');
        });
    
        $this->assertIsCallable($route->controller);
        $this->assertSame($route->url, '/foo');
        $this->assertNull($route->action);
        $this->assertSame($route->method, 'delete');
        $this->assertEmpty($route->middlewares);
    }

    public function testPursueOnAnyFilteredRoute()
    {
        $route = Route::get('/foo', [Controller::class => 'foo'])->middleware(Middleware::class);
    
        $assert = ['App\Middlewares\Middleware' => []];

        $this->assertSame($route->controller, Controller::class);
        $this->assertSame($route->url, '/foo');
        $this->assertSame($route->action, 'foo');
        $this->assertSame($route->method, 'get');
        $this->assertSame($route->middlewares, $assert);
    }

    public function testFailOnInvalidGroup()
    {
        $this->expectException(\InvalidArgumentException::class);

        Route::get('/foo', [Controller::class => 'foo'])
            ->group('invalid');
    }

    public function testFailOnInvalidMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class);

        Route::get('/foo', [Controller::class => 'foo'])
            ->middleware('App\Middlewares\Invalid');
    }
    public function testFailOnInvalidController()
    {
        $this->expectException(\InvalidArgumentException::class);

        Route::get('/foo', ['App\Controllers\Invalid' => 'invalid']);
    }

    public function testPursueOnValidParameters()
    {
        $route = Route::get('/foo', [Controller::class => 'foo'])
            ->middleware(Middleware::class.':foo,bar');

        $this->assertSame($route->middlewares[Middleware::class], [
            0 => 'foo',
            1 => 'bar'
        ]);
    }

    public function testFailOnInvalidParameters()
    {
        $this->expectException(\InvalidArgumentException::class);

        Route::get('/foo', [Controller::class => 'foo'])
            ->middleware(Middleware::class.':>invalid,');
    }
}