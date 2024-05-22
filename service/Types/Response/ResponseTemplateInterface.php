<?php

namespace Revamp\Service\Types\Response;

interface ResponseTemplateInterface
{
    public function __set(string $name, $value);
}