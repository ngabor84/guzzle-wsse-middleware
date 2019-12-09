<?php declare(strict_types=1);

namespace Guzzle\Http\Middleware\Tests;

use Guzzle\Http\Middleware\WsseMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

trait TestHelper
{
    private $clientHistory = [];

    private function createTestClientWithWsseMiddlewareStack(): Client
    {
        $stack = $this->createTestStackWithMiddleware();

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        return new Client(['handler' => $stack]);
    }

    private function createTestStackWithMiddleware(): HandlerStack
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware();
        $stack->push($middleware);

        return $stack;
    }

    private function createTestMiddleware(?\DateTime $dateTime = null): WsseMiddleware
    {
        return new WsseMiddleware('test_user', 'test_pass', $dateTime);
    }

    private function getRequestFromClientHistory($requestNumber = 0): ?RequestInterface
    {
        return $this->clientHistory[$requestNumber]['request'] ?? null;
    }

    private function assertHasValidAuthHeader(RequestInterface $request): void
    {
        $authHeader = $request->getHeader('x-wsse')[0];
        $expectedHeaderParts = ['Username', 'PasswordDigest', 'Nonce', 'Created'];

        foreach ($expectedHeaderParts as $name) {
            $this->assertStringContainsString($name, $authHeader);
        }

        $this->assertIsValidSignature($authHeader);
    }

    private function assertIsValidSignature(string $authHeader): void
    {
        $matches = [];
        preg_match_all("/UsernameToken Username=\"([^\"]+)\" PasswordDigest=\"([^\"]+)\" Nonce=\"([^\"]+)\" Created=\"([^\"]+)\"/", $authHeader, $matches);
        $passwordDigest = $matches[2][0];
        $nonce = $matches[3][0];
        $created = $matches[4][0];
        $expectedPasswordDigest = base64_encode(sha1($nonce . $created . 'test_pass'));

        $this->assertEquals($expectedPasswordDigest, $passwordDigest);
    }
}
