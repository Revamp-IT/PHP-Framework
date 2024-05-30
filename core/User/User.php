<?php

namespace Revamp\Core\User;

use Revamp\Core\Types\DataMap\Required;
use Revamp\Core\Types\DataMap\Unique;
use Revamp\Core\Types\Template\DataMap\DataMapTemplate;

class User extends DataMapTemplate
{
    #[Required]
    #[Unique]
    private string $login;
    #[Required]
    private string $password;
}