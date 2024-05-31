<?php

namespace Revamp\Core\ConfigManager;

final class ConfigManager implements ConfigManagerInterface
{
    private array $env = [];

    public final function __construct()
    {
        $file = file_get_contents(__DIR__ . '/../../.env.example');
        $lines = explode("\n", $file);

        foreach ($lines as $line) {
            $property = explode("=", $line)[0];
            $value = explode("=", $line)[1] ?? "";

            $this->env[$property] = $value;
        }
    }

    public final function get($name): string
    {
        return $this->env[$name] ?? "";
    }
}