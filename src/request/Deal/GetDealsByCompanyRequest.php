<?php

namespace Revamp\App\Request\Deal;

use Revamp\Core\Types\Template\Request\RequestTemplate;

class GetDealsByCompanyRequest extends RequestTemplate
{
    public string $company;
}