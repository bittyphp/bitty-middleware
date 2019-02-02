<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\DefaultHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DefaultHandlerTest extends TestCase
{
    /**
     * @var DefaultHandler
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new DefaultHandler();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandle(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $actual = $this->fixture->handle($request);

        self::assertInstanceOf(ResponseInterface::class, $actual);
        self::assertEquals('Not Found', $actual->getBody());
        self::assertEquals(404, $actual->getStatusCode());
    }
}
