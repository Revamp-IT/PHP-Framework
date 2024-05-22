<?php

namespace Revamp\Service\Token;

use Revamp\Service\ConfigManager\ConfigManagerInterface;
use Revamp\Service\Header\HeaderInterface;

final class Token implements TokenInterface
{
    private string $token;

    public final function __construct(
        private HeaderInterface $header,
        private ConfigManagerInterface $config
    ) {
        $token = $this->header->getHeader('Authorization');

        if ($token) {
            $this->token = explode(" ", $token)[1];
        } else {
            header("HTTP/1.0 401 Unauthorized");
            die();
        }
    }

    public final function getToken(): string
    {
        return $this->token;
    }

    public final function generateToken(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $encodedHeader = base64_encode(json_encode($header));
        $encodedPayload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->config->getSecret());

        return $encodedHeader . '.' . $encodedPayload . '.' . $signature;
    }

    public final function validateToken(): bool
    {
        $parts = explode('.', $this->token);

        return $parts[2] == hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->config->getSecret());
    }
}