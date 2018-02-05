<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\MiddlewareHandler;
use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChainTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MiddlewareChain
     */
    protected $fixture = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getMock(ContainerInterface::class);

        $this->fixture = new MiddlewareChain($this->container);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testDefaultHandler()
    {
        $handler = $this->getMock(RequestHandlerInterface::class);

        $this->fixture->setDefaultHandler($handler);
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
}
