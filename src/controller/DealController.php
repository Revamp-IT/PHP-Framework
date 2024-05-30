<?php

namespace Revamp\App\Controller;

use Revamp\App\Request\Deal\GetDealsByCompanyRequest;
use Revamp\App\Response\Deal\GetDealsByCompanyResponse;
use Revamp\Core\DataMapHandler\DataMapHandlerInterface;
use Revamp\Core\Types\Bruno\Bruno;
use Revamp\Core\Types\Cache\Cache;
use Revamp\Core\Types\Request\Request;
use Revamp\Core\Types\Response\Response;
use Revamp\Core\Types\Route\Route;
use Revamp\Core\Types\Template\Controller\ControllerTemplate;
use Revamp\Core\Types\Token\Authenticate;

#[Bruno(name: 'Deals')]
class DealController extends ControllerTemplate
{
    #[Route(uri: '/deals', methods: ['GET'])]
    #[Request(requestTemplate: GetDealsByCompanyRequest::class)]
    #[Response(responseTemplate: GetDealsByCompanyResponse::class)]
    #[Cache]
    #[Authenticate]
    #[Bruno(name: 'Get Deals by Company')]
    public function getDealsByCompany(
        DataMapHandlerInterface $dmh,
    ): void
    {
        $this->response->deals = ['msg' => 'test2'];
    }
}