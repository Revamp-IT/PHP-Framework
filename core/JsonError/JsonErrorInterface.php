<?php

namespace Revamp\Core\JsonError;

interface JsonErrorInterface
{
    public function throw(array $error): void;
}