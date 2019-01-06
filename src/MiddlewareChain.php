<?php

namespace Bitty\Middleware;

use Bitty\Middleware\DefaultHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareChain implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    protected $chain = [];

    /**
     * @var RequestHandlerInterface
     */
    protected $defaultHandler = null;

    /**
     * @param RequestHandlerInterface|null $defaultHandler
     */
    public function __construct(RequestHandlerInterface $defaultHandler = null)
    {
        if (null === $defaultHandler) {
            $defaultHandler = new DefaultHandler();
        }

        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Adds middleware to the chain.
     *
     * @param MiddlewareInterface $middleware
     */
    public function add(MiddlewareInterface $middleware): void
    {
        $this->chain[] = $middleware;
    }

    /**
     * Sets the default request handler.
     *
     * @param RequestHandlerInterface $defaultHandler
     */
    public function setDefaultHandler(RequestHandlerInterface $defaultHandler): void
    {
        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Gets the default request handler.
     *
     * @return RequestHandlerInterface
     */
    public function getDefaultHandler(): RequestHandlerInterface
    {
        return $this->defaultHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $chain = $this->buildChain();

        return $chain->handle($request);
    }

    /**
     * Builds the request handler chain.
     *
     * @return RequestHandlerInterface
     */
    protected function buildChain(): RequestHandlerInterface
    {
        $chain = $this->defaultHandler;

        foreach (array_reverse($this->chain) as $middleware) {
            $chain = new MiddlewareHandler($middleware, $chain);
        }

        return $chain;
    }
}
