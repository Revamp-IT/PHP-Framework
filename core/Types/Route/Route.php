<?php

namespace Revamp\Core\Types\Route;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(string $uri, array $methods){}
}