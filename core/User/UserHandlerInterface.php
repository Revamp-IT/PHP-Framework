<?php

namespace Revamp\Core\User;

interface UserHandlerInterface
{
    public function register(string $login, string $password): void;
    public function authorize(string $login, string $password): void;
}