<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\MiddlewareHandler;
use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MiddlewareHandler
     */
    protected $fixture = null;

    /**
     * @var MiddlewareInterface
     */
    protected $middleware = null;

    /**
     * @var RequestHandlerInterface
     */
    protected $handler = null;

    protected function setUp()
    {
        parent::setUp();

        $this->middleware = $this->getMock(MiddlewareInterface::class);
        $this->handler    = $this->getMock(RequestHandlerInterface::class);

        $this->fixture = new MiddlewareHandler($this->middleware, $this->handler);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandleCallsMiddleware()
    {
        $request = $this->getMock(ServerRequestInterface::class);

        $this->middleware->expects($this->once())
            ->method('process')
            ->with($request, $this->handler);

        $this->fixture->handle($request);
    }

    public function testMiddlewareResponseReturned()
    {
        $request  = $this->getMock(ServerRequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);

        $this->middleware->method('process')->willReturn($response);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }
}
