<?php

namespace Revamp\Core\DataMapHandler;

use Revamp\Core\Cache\CacheHandlerInterface;
use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\RequestHandler\RequestHandlerInterface;

interface DataMapHandlerInterface
{
    public function __construct(ConfigManagerInterface $config, RequestHandlerInterface $requestHandler, CacheHandlerInterface $cacheHandler);

    public function setMap(string $map): DataMapHandler;
    public function getById(int $id): array;
    public function getBy(array $query, bool $many): array;
    public function insert(array $data): bool;
    public function updateOneById(int $id, array $data): bool;
    public function deleteOneById(int $id): bool;
}