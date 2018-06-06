<?php declare(strict_types=1);

namespace Guzzle\Http\Middleware;

use Psr\Http\Message\RequestInterface;

class WsseMiddleware
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var \DateTime|null
     */
    private $createdAt;

    public function __construct(string $username, string $password, ?\DateTime $createdAt = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->createdAt = $createdAt ?? new \DateTime();
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($options['auth'] == 'wsse') {
                $request = $this->signRequest($request);
            }

            return $handler($request, $options);
        };
    }

    private function signRequest(RequestInterface $request): RequestInterface
    {
        $nonce = $this->generateNonce();
        $createdAt = $this->getCreatedAtString();
        $digest = $this->generateDigest($nonce, $createdAt, $this->password);

        $token = implode(
            ' ',
            [
                sprintf('Username="%s"', $this->username),
                sprintf('PasswordDigest="%s"', $digest),
                sprintf('Nonce="%s"', $nonce),
                sprintf('Created="%s"', $createdAt)
            ]
        );

        $request = $request->withHeader('Authorization', 'WSSE profile="UsernameToken"');
        $request = $request->withHeader('X-WSSE', sprintf('UsernameToken %s', $token));

        return $request;
    }

    private function generateNonce(): string
    {
        return md5(uniqid('', true));
    }

    private function getCreatedAtString(): string
    {
        return $this->createdAt->format('c');
    }

    private function generateDigest(string $nonce, string $createdAt, string $password): string
    {
        return base64_encode(sha1($nonce . $createdAt . $password));
    }
}
