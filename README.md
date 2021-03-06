# Bitty Middleware

[![Build Status](https://travis-ci.org/bittyphp/middleware.svg?branch=master)](https://travis-ci.org/bittyphp/middleware)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/c4439e8d27304c6f96caaec42d252650)](https://www.codacy.com/app/bittyphp/middleware)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Mutation Score](https://badge.stryker-mutator.io/github.com/bittyphp/middleware/master)](https://infection.github.io)
[![Total Downloads](https://poser.pugx.org/bittyphp/middleware/downloads)](https://packagist.org/packages/bittyphp/middleware)
[![License](https://poser.pugx.org/bittyphp/middleware/license)](https://packagist.org/packages/bittyphp/middleware)

Bitty comes with a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware implementation.

## Installation

It's best to install using [Composer](https://getcomposer.org/).

```sh
$ composer require bittyphp/middleware
```

## Official Middleware

Bitty only comes with middleware for the most basic of needs. However, using the `MiddlewareInterface` you can build support for almost anything you can think of.

- [Router](https://github.com/bittyphp/router)
- [Security](https://github.com/bittyphp/security) (Work in Progress)
- [Error Handler](https://github.com/bittyphp/error-handler) (Work in Progress)

## Basic Usage

To use Bitty's middleware outside of Bitty the setup is fairly straightforward. The details of each call will be explained later.

```php
<?php

use Bitty\Middleware\MiddlewareChain;

// Create a middleware chain.
$middleware = new MiddlewareChain();

// Optional: Override the default handler.
$middleware->setDefaultHandler(...);

// Optional: Add your custom middleware.
$middleware->add(...);
$middleware->add(...);
$middleware->add(...);

// Process the request.
$response = $middleware->handle($request);
```

### Default Handler

The default handler is what gets called when nothing else processes the request. It must be an instance of `Psr\Http\Server\RequestHandlerInterface`. By default, this is just a class that returns a 404 Not Found response. You can override the default handler to be anything you want, but you don't have to.

For more information, see the section on Creating a Request Handler.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Psr\Http\Server\RequestHandlerInterface;

/** @var RequestHandlerInterface */
$defaultHandler = ...;

$middleware = new MiddlewareChain($defaultHandler);
```

You can also set the default handler after the middleware chain has been constructed.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Psr\Http\Server\RequestHandlerInterface;

/** @var RequestHandlerInterface */
$defaultHandler = ...;

$middleware = new MiddlewareChain();
$middleware->setDefaultHandler($defaultHandler);
```

### Adding Middleware

All middleware added must implement `Psr\Http\Server\MiddlewareInterface`. The middleware chain is built using a first-in, first-out approach. This means the first middleware you add will be the first middleware that gets called. You can use this to structure your middleware in the order you want.

For more information, see the section on Creating Middleware.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Psr\Http\Server\MiddlewareInterface;

$middleware = new MiddlewareChain();

/** @var MiddlewareInterface */
$someMiddleware = ...;

$middleware->add($someMiddleware);
```

### Processing the Request

The final step is processing the request. The middleware chain will handle any request that implements `Psr\Http\Message\ServerRequestInterface`. This should produce a `Psr\Http\Message\ResponseInterface` which you can then send to the user.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$middleware = new MiddlewareChain();

/** @var ServerRequestInterface */
$request = ...;

/** @var ResponseInterface */
$response = $middleware->handle($request);
```

## Creating a Request Handler

If you'd like to do more than return a simple 404 Not Found response in the event that no middleware processed a request, you'll have to build a custom request handler. You can use this to return an error page, redirect the user to a search page, or display a help page. A request handler can be any class that implements `Psr\Http\Server\RequestHandlerInterface`.

```php
<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SomeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface */
        $response = ...;

        return $response;
    }
}
```

Then you simply set your handler as the default handler for the middleware chain.

```php
<?php

$defaultHandler = new SomeHandler();
$middleware = new MiddlewareChain($defaultHandler);
```

## Creating Middleware

There are two basic approaches to creating a middleware component:

  1. Pre-process middleware
  2. Post-process middleware

### Pre-process Middleware

This middleware style intercepts all requests and has the ability to prevent further middleware from being called. It can also be used to modify the request before passing it along to the next handler. A security layer might use this style of middleware, as it can determine if a user is authorized to perform an action and stop the request from continuing if needed.

```php
<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (/* able to process request */) {
            /** @var ResponseInterface */
            $response = ...;

            return $response;
        }

        // You can modify the request before passing it along
        $newRequest = $request->withHeader('X-Validated', 'true');

        // if unable to handle request, call the next middleware handler
        return $handler->handle($newRequest);
    }
}
```

### Post-process Middleware

Alternatively, you can use a post-process middleware that allows all other middleware to be called and then it can intercept the response before it's returned to the user. Something like this might be used for an API to always add a JSON content type if the request was to an API resource.

```php
<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface */
        $response = $handler->handle($request);

        if (/* request meets criteria */) {
            return $response->withHeader('Content-type', 'application/json');
        }

        return $response;
    }
}
```
