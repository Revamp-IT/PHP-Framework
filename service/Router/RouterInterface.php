<?php

namespace Revamp\Service\Router;

use Revamp\Service\Container\ContainerInterface;

interface RouterInterface
{
    public function __construct(ContainerInterface $container);
}