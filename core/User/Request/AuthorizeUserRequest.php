<?php

namespace Revamp\Core\User\Request;

use Revamp\Core\Types\Template\Request\RequestTemplate;

class AuthorizeUserRequest extends RequestTemplate
{
    public string $login;
    public string $password;
}