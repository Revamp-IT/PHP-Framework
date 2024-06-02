<?php

namespace Revamp\Core\Container;

interface ContainerInterface
{
    public function __construct();
    public function get(string $interfaceOrClass): object;
    public function register(string $interface, string $class): void;
}