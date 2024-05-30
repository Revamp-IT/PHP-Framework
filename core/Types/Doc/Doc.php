<?php

namespace Revamp\Core\Types\Doc;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Doc
{
    public function __construct(string $name, string $description = null) {}
}