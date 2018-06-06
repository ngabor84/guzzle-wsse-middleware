[ ![Codeship Status for ngabor84/guzzle-wsse-middleware](https://app.codeship.com/projects/bb473260-4b7f-0136-d461-0a55bf1c344a/status?branch=master)](https://app.codeship.com/projects/292946)
[![GitHub license](https://img.shields.io/github/license/ngabor84/guzzle-wsse-middleware.svg)](https://github.com/ngabor84/guzzle-wsse-middleware/blob/master/LICENSE.md)

# Guzzle WSSE Middleware

This authentication middleware add WSSE sign functionality to Guzzle Http Client.

## Installation
`composer require ngabor84/guzzle-wsse-middleware`

## Usage
```php
<?php

$wsseMiddleware = new \Guzzle\Http\Middleware\WsseMiddleware($username, $password);

$stack = \GuzzleHttp\HandlerStack::create();

$stack->push($wsseMiddleware);

$client   = new \GuzzleHttp\Client(['handler' => $stack]);

// Important: set the auth option to escher to activate the middleware
$response = $client->get('http://www.8points.de', ['auth' => 'wsse']);
```