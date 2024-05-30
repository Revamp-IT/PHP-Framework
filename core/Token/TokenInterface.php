<?php

namespace Revamp\Core\Token;

use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\Header\HeaderInterface;

interface TokenInterface
{
    public function __construct(
        ConfigManagerInterface $config
    );
    public function generateAccessToken(array $payload, int $seconds): string;
    public function generateRefreshToken(string $accessToken, int $seconds): string;
    public function validateToken(string $token): bool;
}