<?php

namespace Revamp\Service\Types\Response;

use Exception;

abstract class ResponseTemplate implements ResponseTemplateInterface
{
    public final function __set(string $name, $value)
    {
        throw new Exception("Non-existing field {$name}");
    }
}