<?php

namespace Revamp\Core\Types\Template\Controller;

use Revamp\Core\Types\Template\Request\RequestTemplateInterface;
use Revamp\Core\Types\Template\Response\ResponseTemplateInterface;
use stdClass;

interface ControllerTemplateInterface
{
    public function __construct(
        stdClass $params,
        RequestTemplateInterface $request,
        ResponseTemplateInterface $response,
    );

    public function getResponse(): ResponseTemplateInterface;
}