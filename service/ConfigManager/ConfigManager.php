<?php

namespace Revamp\Service\ConfigManager;

final class ConfigManager implements ConfigManagerInterface
{
    private array $env = [];

    public final function __construct()
    {
        $file = file_get_contents(__DIR__ . '/../../.env');
        $lines = explode("\n", $file);

        foreach ($lines as $line) {
            $property = explode("=", $line)[0];
            $value = explode("=", $line)[1] ?? "";

            $this->env[$property] = $value;
        }
    }

    public final function getDatabaseHost(): string
    {
        return $this->env['DATABASE_HOST'];
    }

    public final function getDatabasePort(): string
    {
        return $this->env['DATABASE_PORT'];
    }

    public final function getDatabaseName(): string
    {
        return $this->env['DATABASE_NAME'];
    }

    public final function getDatabaseUser(): string
    {
        return $this->env['DATABASE_USER'];
    }

    public final function getDatabasePassword(): string
    {
        return $this->env['DATABASE_PASS'];
    }

    public final function getSecret(): string
    {
        return $this->env['SECRET'];
    }
}