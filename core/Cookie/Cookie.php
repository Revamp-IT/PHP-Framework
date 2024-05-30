<?php

namespace Revamp\Core\Cookie;

class Cookie implements CookieInterface
{
    public final function get(string $name): string
    {
        return $_COOKIE[$name] ?? "";
    }

    public final function set(string $name, string $value): void
    {
        setcookie(name: $name, value: $value, httponly: true);
    }
}