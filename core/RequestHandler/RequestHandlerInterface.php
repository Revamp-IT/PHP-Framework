<?php

namespace Revamp\Core\RequestHandler;

interface RequestHandlerInterface
{
    public function setParams(string $pattern): void;
    public function getMethod(): string;
    public function getIp(): string;
    public function getUri(): string;
    public function getBody(): array;
    public function getParams(): array;
}