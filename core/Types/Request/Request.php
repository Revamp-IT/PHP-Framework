<?php

namespace Revamp\Core\Types\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Request
{
    public function __construct(string $requestTemplate) {}
}