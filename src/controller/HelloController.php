<?php

namespace Revamp\App\Controller;

use Revamp\App\Helper\HelloHelper;
use Revamp\App\Request\Hello\HelloRequest;
use Revamp\App\Response\Hello\HelloResponse;
use Revamp\Core\Types\Bruno\Bruno;
use Revamp\Core\Types\Cache\Cache;
use Revamp\Core\Types\Request\Request;
use Revamp\Core\Types\Response\Response;
use Revamp\Core\Types\Route\Route;
use Revamp\Core\Types\Template\Controller\ControllerTemplate;

#[Bruno(name: 'User')]
class HelloController extends ControllerTemplate
{
    #[Route(uri: '/hello/{id}/{name}', methods: ['GET'])]
    #[Request(requestTemplate: HelloRequest::class)]
    #[Response(responseTemplate: HelloResponse::class)]
    #[Cache]
    #[Bruno(name: 'Send Hello')]
    public function sendHello(HelloHelper $helper): void
    {
        $this->response->data = "{$helper->getString()}, {$this->params->name}! Your ID: {$this->params->id}";
    }
}