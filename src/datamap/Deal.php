<?php

namespace Revamp\App\DataMap;

use Revamp\Core\Types\DataMap\DataMap;

class Deal extends DataMap
{
    private string $name;
    private int $price;
    private int $manager;
    private int $customer;
    private int $column;
    private int $company;
}