<?php

namespace Revamp\Core\Types\Response;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Response
{
    public function __construct(string $responseTemplate) {}
}