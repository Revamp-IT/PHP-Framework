<?php

namespace Revamp\Core\Container;

interface ContainerInterface
{
    public function __construct();
    public function get(string $interface ): object;
    public function registerCore(string $interface, string $class): void;
}