<?php

namespace Guzzle\Http\Middleware\Tests;

use Guzzle\Http\Middleware\WsseMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;

class WsseMiddlewareTest extends TestCase
{

    use TestHelper;

    /**
     * @test
     */
    public function construct_CreateNewWsseMiddleware()
    {
        $middleware = $this->createTestMiddleware();
        $this->assertInstanceOf(WsseMiddleware::class, $middleware);
    }

    /**
     * @test
     */
    public function invoke_SignTheRequest_WhenWsseMiddlewareAddedAndAuthMethodIsWsse()
    {
        $client = $this->createTestClientWithWsseMiddlewareStack();
        $client->post('http://httpbin.org/post', [
            'auth' => 'wsse',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('authorization'));
        $this->assertTrue($request->hasHeader('x-wsse'));
        $this->assertHasValidAuthHeader($request);
    }

    /**
     * @test
     */
    public function invoke_NotSignTheRequest_WhenWsseMiddlewareAddedButAuthMethodIsNotWsse()
    {
        $client = $this->createTestClientWithWsseMiddlewareStack();
        $client->post('http://httpbin.org/post', [
            'auth' => 'other_auth',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertFalse($request->hasHeader('authorization'));
        $this->assertFalse($request->hasHeader('x-wsse'));
    }

    /**
     * @test
     */
    public function invoke_SignWithGivenDateTime_WhenSetCustomDateTimeOnConstruct()
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware(new \DateTime("2018-01-01 11:11:11"));
        $stack->push($middleware);

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $client->post('http://httpbin.org/post', [
            'auth' => 'wsse',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('authorization'));
        $this->assertTrue($request->hasHeader('x-wsse'));
        $authHeader = $request->getHeader('x-wsse')[0];
        $this->assertStringContainsString('Created="2018-01-01T11:11:11+00:00"', $authHeader);
    }
}
