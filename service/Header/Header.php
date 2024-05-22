<?php

namespace Revamp\Service\Header;

final class Header implements HeaderInterface
{
    private array $headers;

    public final function __construct()
    {
        $this->headers = getallheaders();
    }

    public final function getHeader(string $name): string|false
    {
        return $this->headers[$name] ?? false;
    }
}