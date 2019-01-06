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

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new MiddlewareChain();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testDefaultHandler(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $actual = $this->fixture->handle($request);

        self::assertInstanceOf(ResponseInterface::class, $actual);
        self::assertEquals('Not Found', $actual->getBody());
        self::assertEquals(404, $actual->getStatusCode());
    }

    public function testCustomDefaultHandler(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->fixture->setDefaultHandler($handler);
        $actual = $this->fixture->getDefaultHandler();

        self::assertSame($handler, $actual);
    }

    public function testCustomDefaultHandlerViaConstructor(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->fixture = new MiddlewareChain($handler);

        $actual = $this->fixture->getDefaultHandler();

        self::assertSame($handler, $actual);
    }

    public function testNoMiddlewareCallsDefaultHandler(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $handler->expects(self::once())
            ->method('handle')
            ->with($request);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testOneMiddleware(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects(self::once())
            ->method('process')
            ->with($request, $handler);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testMultipleMiddlewares(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $middlewareA = $this->createMock(MiddlewareInterface::class);
        $middlewareB = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middlewareA);
        $this->fixture->add($middlewareB);

        $middlewareA->expects(self::once())
            ->method('process')
            ->with($request, self::isInstanceOf(MiddlewareHandler::class));

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testResponse(): void
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->method('process')->willReturn($response);
        $this->fixture->add($middleware);

        $actual = $this->fixture->handle($request);

        self::assertSame($response, $actual);
    }
}
