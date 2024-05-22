<?php

namespace Revamp\Service\DataMapHandler;

use Revamp\Service\ConfigManager\ConfigManagerInterface;

interface DataMapHandlerInterface
{
    public function __construct(ConfigManagerInterface $config);

    public function setMap(string $map): DataMapHandler;
    public function getById(int $id): array;
    public function getBy(array $query): array;
    public function insert(): void;
}