<?php

namespace Revamp\Core\Router;

use Revamp\Core\RequestHandler\RequestHandlerInterface;

class Router implements RouterInterface
{
    public function __construct(
        private RequestHandlerInterface $requestHandler
    )
    {
    }


}