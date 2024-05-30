<?php

namespace Revamp\Core\Types\Cache;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cache
{
    public function __construct() {}
}