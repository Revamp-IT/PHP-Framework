<?php

namespace Revamp\Service\Types\Dependency;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Dependency
{
    public function __construct(string $dependency) {}
}