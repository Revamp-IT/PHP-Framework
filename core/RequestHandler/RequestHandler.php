<?php

namespace Revamp\Core\RequestHandler;

class RequestHandler implements RequestHandlerInterface
{
    private string $method;
    private string $ip;
    private string $uri;
    private array $body;
    private array $params;

    public function __construct()
    {
        $this->setMethod();
        $this->setIp();
        $this->setUri();
        $this->setBody();
    }

    private function setMethod(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    private function setIp(): void
    {
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    private function setUri(): void
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (!str_ends_with($uri, '/')) $uri .= '/';
        $this->uri = $uri;
    }

    private function setBody(): void
    {
        $this->body = json_decode(file_get_contents('php://input'), true);
    }

    public function setParams(string $pattern): void
    {
        $patternParts = explode('/', $pattern);
        $uriParts = explode('/', $this->uri);

        $params = [];

        $i = 0;
        foreach ($patternParts as $part) {
            if (str_starts_with($part, '{')) $params[substr($part, 1, -1)] = $uriParts[$i];
            $i++;
        }

        $this->params = $params;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}