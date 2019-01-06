<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\MiddlewareHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareHandlerTest extends TestCase
{
    /**
     * @var MiddlewareHandler
     */
    protected $fixture = null;

    /**
     * @var MiddlewareInterface|MockObject
     */
    protected $middleware = null;

    /**
     * @var RequestHandlerInterface|MockObject
     */
    protected $handler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = $this->createMock(MiddlewareInterface::class);
        $this->handler    = $this->createMock(RequestHandlerInterface::class);

        $this->fixture = new MiddlewareHandler($this->middleware, $this->handler);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandleCallsMiddleware(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->middleware->expects(self::once())
            ->method('process')
            ->with($request, $this->handler);

        $this->fixture->handle($request);
    }

    public function testMiddlewareResponseReturned(): void
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->middleware->method('process')->willReturn($response);

        $actual = $this->fixture->handle($request);

        self::assertSame($response, $actual);
    }
}
