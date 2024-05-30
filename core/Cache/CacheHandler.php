<?php

namespace Revamp\Core\Cache;

use Revamp\Core\ConfigManager\ConfigManagerInterface;

use Predis\Client;

final class CacheHandler implements CacheHandlerInterface
{
    private Client $client;

    public final function __construct(private ConfigManagerInterface $config)
    {
        $this->client = new Client([
            'host' => $this->config->get('REDIS_HOST'),
            'port' => $this->config->get('REDIS_PORT'),
            'username' => $this->config->get('REDIS_USER'),
            'password' => $this->config->get('REDIS_PASS'),
        ]);
    }

    public final function buildKey(string $uri, array $body): string
    {
        return str_replace('/', ':', $uri) . "?" . http_build_query($body);
    }

    public final function get(string $key): string
    {
        return $this->client->get($key) ?? "";
    }

    public final function set(string $key, string $value): void
    {
        $this->client->set($key, $value);
    }
}