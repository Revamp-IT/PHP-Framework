<?php

namespace Revamp\Core\Types\Template\Controller;

use Revamp\Core\Types\Template\Request\RequestTemplateInterface;
use Revamp\Core\Types\Template\Response\ResponseTemplateInterface;

interface ControllerTemplateInterface
{
    public function __construct(
        RequestTemplateInterface $request,
        ResponseTemplateInterface $response,
    );

    public function getResponse(): ResponseTemplateInterface;
}