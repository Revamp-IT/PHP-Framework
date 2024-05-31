<?php

namespace Revamp\App\Controller;

use Revamp\App\Request\Hello\HelloRequest;
use Revamp\App\Response\Hello\HelloResponse;
use Revamp\Core\Types\Bruno\Bruno;
use Revamp\Core\Types\Request\Request;
use Revamp\Core\Types\Response\Response;
use Revamp\Core\Types\Route\Route;
use Revamp\Core\Types\Template\Controller\ControllerTemplate;

#[Bruno(name: 'User')]
class HelloController extends ControllerTemplate
{
    #[Route(uri: '/hello', methods: ['POST'])]
    #[Request(requestTemplate: HelloRequest::class)]
    #[Response(responseTemplate: HelloResponse::class)]
    #[Bruno(name: 'Send Hello')]
    public function sendHello(): void
    {
        $this->response->data = "Hello, {$this->request->name}";
    }
}