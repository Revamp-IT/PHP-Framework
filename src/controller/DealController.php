<?php

namespace Revamp\App\Controller;

use Revamp\App\DataMap\Deal;
use Revamp\App\Request\Deal\GetDealsByCompanyRequest;
use Revamp\App\Response\Deal\GetDealsByCompanyResponse;
use Revamp\Service\DataMapHandler\DataMapHandlerInterface;
use Revamp\Service\Types\Controller\ControllerTemplate;

use Revamp\Service\Types\Bruno\Bruno;
use Revamp\Service\Types\Request\Request;
use Revamp\Service\Types\Response\Response;
use Revamp\Service\Types\Route\Route;
use Revamp\Service\Types\Token\Authenticate;

#[Bruno(name: 'Deals')]
class DealController extends ControllerTemplate
{
    #[Route(uri: '/deals', methods: ['GET'])]
    #[Request(requestTemplate: GetDealsByCompanyRequest::class)]
    #[Response(responseTemplate: GetDealsByCompanyResponse::class)]
    #[Authenticate]
    #[Bruno(name: 'Get Deals by Company')]
    public function getDealsByCompany(
        DataMapHandlerInterface $dmh,
    ): void
    {
        $this->response->deals = $dmh->setMap(Deal::class)->getBy(['company' => $this->request->company]);
    }
}