<?php

namespace Revamp\App\Request\Deal;

use Revamp\Service\Types\Request\RequestTemplate;

class GetDealsByCompanyRequest extends RequestTemplate
{
    public string $company;
}