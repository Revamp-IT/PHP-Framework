<?php

namespace Revamp\App\Controller;

use Revamp\Core\Types\Bruno\Bruno;
use Revamp\Core\Types\Request\Request;
use Revamp\Core\Types\Response\Response;
use Revamp\Core\Types\Route\Route;
use Revamp\Core\Types\Template\Controller\ControllerTemplate;
use Revamp\Core\User\Request\AuthorizeUserRequest;
use Revamp\Core\User\Request\CreateUserRequest;
use Revamp\Core\User\Response\AuthorizeUserResponse;
use Revamp\Core\User\Response\CreateUserResponse;
use Revamp\Core\User\UserHandlerInterface;

#[Bruno(name: 'User')]
class HelloController extends ControllerTemplate
{
    #[Route(uri: '/hello', methods: ['POST'])]
    #[Request(requestTemplate: CreateUserRequest::class)]
    #[Response(responseTemplate: CreateUserResponse::class)]
    #[Bruno(name: 'Create User')]
    public function register(): void
    {

    }

    #[Route(uri: '/authorize', methods: ['POST'])]
    #[Request(requestTemplate: AuthorizeUserRequest::class)]
    #[Response(responseTemplate: AuthorizeUserResponse::class)]
    #[Bruno(name: 'Authorize User')]
    public function authorize(UserHandlerInterface $user): void
    {
       $user->authorize($this->request->login, $this->request->password);
    }
}