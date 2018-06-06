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