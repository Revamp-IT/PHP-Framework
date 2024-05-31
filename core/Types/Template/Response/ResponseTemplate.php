<?php

namespace Revamp\Core\Types\Template\Response;

use Exception;

abstract class ResponseTemplate implements ResponseTemplateInterface
{
    public final function __set(string $name, $value)
    {
        throw new Exception("Non-existing field {$name}");
    }

    public final function fill(array $data): void
    {
        unset($data['id']);

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}