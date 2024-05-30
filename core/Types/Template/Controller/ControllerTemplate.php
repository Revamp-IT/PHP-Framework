<?php

namespace Revamp\Core\Types\Template\Controller;

use Revamp\Core\Types\Template\Request\RequestTemplateInterface;
use Revamp\Core\Types\Template\Response\ResponseTemplateInterface;
use stdClass;

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