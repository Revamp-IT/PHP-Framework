<?php

namespace Revamp\Core\DataMapHandler;

use Revamp\Core\ConfigManager\ConfigManagerInterface;

interface DataMapHandlerInterface
{
    public function __construct(ConfigManagerInterface $config);

    public function setMap(string $map): DataMapHandler;
    public function getById(int $id): array;
    public function getBy(array $query, bool $many): array;
    public function insert(array $data): bool;
}