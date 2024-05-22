<?php

namespace Revamp\Service\Token;

use Revamp\Service\ConfigManager\ConfigManagerInterface;
use Revamp\Service\Header\HeaderInterface;

interface TokenInterface
{
    public function __construct(
        HeaderInterface $header,
        ConfigManagerInterface $config
    );
    public function getToken(): string;
    public function generateToken(array $payload): string;
    public function validateToken(): bool;
}