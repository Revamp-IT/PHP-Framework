<?php

namespace Revamp\Core\Cache;

use Revamp\Core\ConfigManager\ConfigManagerInterface;

interface CacheHandlerInterface
{
    public function __construct(ConfigManagerInterface $config);
    public function buildKey(string $uri, array $body): string;
    public function get(string $key): string;
    public function set(string $key, string $value): void;
}