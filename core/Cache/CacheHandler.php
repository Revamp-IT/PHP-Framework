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

    public final function buildKey(string $uri, array $params): string
    {
        $uriParts = explode('/', $uri);

        $index = 0;
        foreach ($uriParts as $part) {
            if (ctype_digit($part)) {
                unset($uriParts[$index]);
            }

            $index++;
        }

        return implode(':', $uriParts) . '#{' . implode(',', array_values($params)) . '}';
    }

    public final function get(string $key): string
    {
        return $this->client->get($key) ?? "";
    }

    public final function set(string $key, string $value): void
    {
        $this->client->set($key, $value);
    }

    public final function keys(string $pattern): array
    {
        return $this->client->keys($pattern);
    }

    public final function delete(string|array $keys): void
    {
        $this->client->del($keys);
    }
}