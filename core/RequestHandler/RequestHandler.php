<?php

namespace Revamp\Core\RequestHandler;

class RequestHandler implements RequestHandlerInterface
{
    private string $method;
    private string $ip;
    private string $uri;
    private array $body;

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
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    private function setBody(): void
    {
        $this->body = json_decode(file_get_contents('php://input'), true);
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
}