<?php

namespace Revamp\Core\Types\Bruno;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Bruno
{
    public function __construct(string $name) {}
}