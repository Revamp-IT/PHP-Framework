<?php

namespace Revamp\Core\Token;

use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\Header\HeaderInterface;

final class Token implements TokenInterface
{
    public final function __construct(
        private ConfigManagerInterface $config
    ) {}

    private function encodeToken(array $payload, int $seconds): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
            'time' => time() + $seconds,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];

        $encodedHeader = base64_encode(json_encode($header));
        $encodedPayload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->config->get('SECRET'));

        return $encodedHeader . '.' . $encodedPayload . '.' . $signature;
    }

    public final function generateAccessToken(array $payload, int $seconds = 60 * 10): string
    {
        return $this->encodeToken($payload, $seconds);
    }

    public final function generateRefreshToken(string $accessToken, int $seconds = 60 * 60 * 24): string
    {
        return $this->encodeToken(['accessToken' => $accessToken], $seconds);
    }

    public final function getPart(string $token, int $part, $decoded = true): array|string
    {
        $parts = explode('.', $token);

        if (!$decoded) return $parts[$part - 1];

        return json_decode(base64_decode($parts[$part - 1]), true);
    }

    public final function validateToken(string $token): bool
    {
        $header = $this->getPart($token, 1);

        if ($header['time'] < time() || $header['ip'] != $_SERVER['REMOTE_ADDR']) return false;

        return $this->getPart($token, 3, false) == hash_hmac('sha256', $this->getPart($token, 1, false) . '.' . $this->getPart($token, 2, false), $this->config->get('SECRET'));
    }
}