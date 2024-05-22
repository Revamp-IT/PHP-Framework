<?php

namespace Revamp\Service\Types\Controller;

use stdClass;
use Revamp\Service\Types\Request\RequestTemplateInterface;
use Revamp\Service\Types\Response\ResponseTemplateInterface;

abstract class ControllerTemplate extends stdClass implements ControllerTemplateInterface
{
    public final function __construct(
        protected RequestTemplateInterface  $request,
        protected ResponseTemplateInterface $response,
    ) {}

    public final function getResponse(): ResponseTemplateInterface
    {
        return $this->response;
    }
}