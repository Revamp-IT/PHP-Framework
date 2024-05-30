<?php

namespace Revamp\Core\Header;

final class Header implements HeaderInterface
{
    private array $requestHeaders;
    private array $responseHeaders;

    public final function __construct()
    {
        $this->requestHeaders = getallheaders();
    }

    public final function getRequestHeader(string $name): string|false
    {
        return $this->requestHeaders[$name] ?? false;
    }

    public final function setResponseHeader(string $header): void
    {
        $this->responseHeaders[] = $header;
    }

    public final function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }
}