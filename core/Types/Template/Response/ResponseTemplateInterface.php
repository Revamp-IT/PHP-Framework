<?php

namespace Revamp\Core\Types\Template\Response;

interface ResponseTemplateInterface
{
    public function __set(string $name, $value);
    public function fill(array $data): void;
}