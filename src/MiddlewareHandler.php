<?php

namespace Bitty\Middleware;

use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareHandler implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface
     */
    protected $middleware = null;

    /**
     * @var RequestHandlerInterface
     */
    protected $handler = null;

    /**
     * @param MiddlewareInterface $middleware
     * @param RequestHandlerInterface $handler
     */
    public function __construct(
        MiddlewareInterface $middleware,
        RequestHandlerInterface $handler
    ) {
        $this->middleware = $middleware;
        $this->handler    = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        return $this->middleware->process($request, $this->handler);
    }
}
