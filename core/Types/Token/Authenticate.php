<?php

namespace Revamp\Core\Types\Token;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Authenticate
{
    public function __construct() {}
}