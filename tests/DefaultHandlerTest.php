<?php

namespace Bitty\Tests\Middleware;

use Bitty\Middleware\DefaultHandler;
use Bitty\Middleware\RequestHandlerInterface;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DefaultHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultHandler
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new DefaultHandler();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandle()
    {
        $request = $this->getMock(ServerRequestInterface::class);

        $actual = $this->fixture->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals('Not Found', $actual->getBody());
        $this->assertEquals(404, $actual->getStatusCode());
    }
}
