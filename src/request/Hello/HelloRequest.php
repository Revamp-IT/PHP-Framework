<?php

namespace Revamp\App\Request\Hello;

use Revamp\Core\Types\Template\Request\RequestTemplate;

class HelloRequest extends RequestTemplate
{
    public string $name;
}