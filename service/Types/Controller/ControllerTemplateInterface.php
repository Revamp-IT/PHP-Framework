<?php

namespace Revamp\Service\Types\Controller;

use Revamp\Service\Types\Request\RequestTemplateInterface;
use Revamp\Service\Types\Response\ResponseTemplateInterface;

interface ControllerTemplateInterface
{
    public function __construct(
        RequestTemplateInterface $request,
        ResponseTemplateInterface $response,
    );

    public function getResponse(): ResponseTemplateInterface;
}