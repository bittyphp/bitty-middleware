# Bitty Middleware

[![Build Status](https://travis-ci.org/bittyphp/bitty-middleware.svg?branch=master)](https://travis-ci.org/bittyphp/bitty-middleware)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/c4439e8d27304c6f96caaec42d252650)](https://www.codacy.com/app/bittyphp/bitty-middleware)
[![Total Downloads](https://poser.pugx.org/bittyphp/bitty-middleware/downloads)](https://packagist.org/packages/bittyphp/bitty-middleware)
[![License](https://poser.pugx.org/bittyphp/bitty-middleware/license)](https://packagist.org/packages/bittyphp/bitty-middleware)

Bitty comes with a middleware implementation that follows the original, [**proposed**  PSR-15](https://github.com/php-fig/fig-standards/blob/d76ac29c3ddc21304ccd0cffa712fa77f09254e0/proposed/http-handlers/request-handlers.md) standard. It does not follow the [accepted PSR-15](https://www.php-fig.org/psr/psr-15/), which added return type hinting that breaks compatibility with PHP 5.

## Installation

It's best to install using [Composer](https://getcomposer.org/).

```sh
$ composer require bittyphp/bitty-middleware
```

## Official Middleware

Bitty only comes with middleware for the most basic of needs. However, using the `MiddlewareInterface` you can build support for almost anything you can think of.

- [Security](https://github.com/bittyphp/bitty-security)
- [Error Handler](https://github.com/bittyphp/bitty-error-handler)

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

The default handler is what gets called when nothing else processes the request. It must be an instance of `Bitty\Middleware\RequestHandlerInterface`. By default, this is just a class that returns a 404 Not Found response. You can override the default handler to be anything you want, but you don't have to.

For more information, see the section on Creating a Request Handler.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\RequestHandlerInterface;

/** @var RequestHandlerInterface */
$defaultHandler = ...;

$middleware = new MiddlewareChain($defaultHandler);
```

You can also set the default handler after the middleware chain has been constructed.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\RequestHandlerInterface;

/** @var RequestHandlerInterface */
$defaultHandler = ...;

$middleware = new MiddlewareChain();
$middleware->setDefaultHandler($defaultHandler);
```

### Adding Middleware

All middleware added must implement `Bitty\Middleware\MiddlewareInterface`. The middleware chain is built using a first-in, first-out approach. This means the first middleware you add will be the first middleware that gets called. You can use this to structure your middleware in the order you want.

For more information, see the section on Creating Middleware.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\MiddlewareInterface;

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

If you'd like to do more than return a simple 404 Not Found response in the event that no middleware processed a request, you'll have to build a custom request handler. You can use this to return a custom error page, redirect the user to a search page, or display a help page. A request handler can be any class that implements `Bitty\Middleware\RequestHandlerInterface`.

```php
<?php

use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SomeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request)
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

namespace Bitty\Middleware;

use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
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

namespace Bitty\Middleware;

use Bitty\Middleware\MiddlewareInterface;
use Bitty\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
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
