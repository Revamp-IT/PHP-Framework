<?php

namespace Revamp\Core\RequestHandler;

interface RequestHandlerInterface
{
    public function getMethod(): string;
    public function getIp(): string;
    public function getUri(): string;
    public function getBody(): array;
}