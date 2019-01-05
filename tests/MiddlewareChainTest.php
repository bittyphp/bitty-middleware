<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\MiddlewareHandler;
use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChainTest extends TestCase
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
        $request = $this->createMock(ServerRequestInterface::class);

        $actual = $this->fixture->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals('Not Found', $actual->getBody());
        $this->assertEquals(404, $actual->getStatusCode());
    }

    public function testCustomDefaultHandler()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->fixture->setDefaultHandler($handler);
        $actual = $this->fixture->getDefaultHandler();

        $this->assertSame($handler, $actual);
    }

    public function testCustomDefaultHandlerViaConstructor()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->fixture = new MiddlewareChain($handler);

        $actual = $this->fixture->getDefaultHandler();

        $this->assertSame($handler, $actual);
    }

    public function testNoMiddlewareCallsDefaultHandler()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testOneMiddleware()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $handler);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testMultipleMiddlewares()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $middlewareA = $this->createMock(MiddlewareInterface::class);
        $middlewareB = $this->createMock(MiddlewareInterface::class);
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
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->method('process')->willReturn($response);
        $this->fixture->add($middleware);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }
}
