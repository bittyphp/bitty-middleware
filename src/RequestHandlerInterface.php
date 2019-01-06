<?php

namespace Bitty\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * An HTTP request handler processes an HTTP request and produces an HTTP response.
 * This interface defines the methods required to use the request handler.
 *
 * NOTE: This is a placeholder until PSR-15 is approved.
 */
interface RequestHandlerInterface
{
    /**
     * Handles the request and returns a response.
     *
     * @param ServerRequestInterface $request Request to handle.
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
