<?php

namespace Revamp\Service\Header;

interface HeaderInterface
{
    public function __construct();
    public function getHeader(string $name): string|false;
}