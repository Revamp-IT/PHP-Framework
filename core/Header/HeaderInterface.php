<?php

namespace Revamp\Core\Header;

interface HeaderInterface
{
    public function __construct();
    public function getRequestHeader(string $name): string|false;
    public function setResponseHeader(string $header): void;
    public function getResponseHeaders(): array;
}