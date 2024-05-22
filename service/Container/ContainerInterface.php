<?php

namespace Revamp\Service\Container;

interface ContainerInterface
{
    public function __construct();
    public function get(string $interface ): object;
    public function registerService(string $interface, string $class): void;
}