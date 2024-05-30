<?php

namespace Revamp\Core\Cookie;

interface CookieInterface
{
    public function get(string $name): string;
    public function set(string $name, string $value): void;
}