<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\MiddlewareHandler;
use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChainTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MiddlewareChain
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new MiddlewareChain();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testDefaultHandler()
    {
        $request = $this->getMock(ServerRequestInterface::class);

        $actual = $this->fixture->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals('Not Found', $actual->getBody());
        $this->assertEquals(404, $actual->getStatusCode());
    }

    public function testCustomDefaultHandler()
    {
        $handler = $this->getMock(RequestHandlerInterface::class);

        $this->fixture->setDefaultHandler($handler);
        $actual = $this->fixture->getDefaultHandler();

        $this->assertSame($handler, $actual);
    }

    public function testCustomDefaultHandlerViaConstructor()
    {
        $handler = $this->getMock(RequestHandlerInterface::class);

        $this->fixture = new MiddlewareChain($handler);

        $actual = $this->fixture->getDefaultHandler();

        $this->assertSame($handler, $actual);
    }

    public function testNoMiddlewareCallsDefaultHandler()
    {
        $handler = $this->getMock(RequestHandlerInterface::class);
        $request = $this->getMock(ServerRequestInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testOneMiddleware()
    {
        $handler = $this->getMock(RequestHandlerInterface::class);
        $request = $this->getMock(ServerRequestInterface::class);

        $middleware = $this->getMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $handler);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testMultipleMiddlewares()
    {
        $handler = $this->getMock(RequestHandlerInterface::class);
        $request = $this->getMock(ServerRequestInterface::class);

        $middlewareA = $this->getMock(MiddlewareInterface::class);
        $middlewareB = $this->getMock(MiddlewareInterface::class);
        $this->fixture->add($middlewareA);
        $this->fixture->add($middlewareB);

        $middlewareA->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(MiddlewareHandler::class));

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testResponse()
    {
        $request  = $this->getMock(ServerRequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);

        $middleware = $this->getMock(MiddlewareInterface::class);
        $middleware->method('process')->willReturn($response);
        $this->fixture->add($middleware);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }
}
