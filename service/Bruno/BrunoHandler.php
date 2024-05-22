<?php

namespace Revamp\Service\Bruno;

use Revamp\Service\Types\Bruno\Bruno;
use Revamp\Service\Types\Controller\ControllerTemplate;

use Revamp\Service\Types\Request\Request;
use Revamp\Service\Types\Route\Route;
use ReflectionClass;

final class BrunoHandler implements BrunoHandlerInterface
{
    private array $controllers;

    public final function __construct()
    {
        $this
            ->getControllers()
            ->makeFiles();
    }

    private function getControllers(): BrunoHandler
    {
        $controllerFiles = array_diff(scandir(__DIR__ . '/../../src/Controller/'), array('..', '.'));
        foreach ($controllerFiles as $controllerFile) {
            include __DIR__ . '/../../src/Controller/' . $controllerFile;
        }

        $this->controllers = $this->getSubclasses(ControllerTemplate::class);

        return $this;
    }

    private function makeFiles(): BrunoHandler
    {
        foreach ($this->controllers as $controller) {
            $class = new ReflectionClass($controller);
            $attribute = $class->getAttributes(Bruno::class)[0];
            $name = $attribute->getArguments()['name'];

            $folderName = __DIR__ . '/../../bruno/' . $name;
            mkdir($folderName);

            foreach ($class->getMethods() as $method) {
                $attribute = $method->getAttributes(Bruno::class)[0];

                if ($attribute) {
                    $name = $attribute->getArguments()['name'];

                    $fileName = $folderName . '/' . $name . '.bru';
                    touch($fileName);

                    $requestMethod = strtolower($method->getAttributes(Route::class)[0]->getArguments()['methods'][0]);
                    $requestUri = $method->getAttributes(Route::class)[0]->getArguments()['uri'];
                    $requestTemplate = $method->getAttributes(Request::class)[0]->getArguments()['requestTemplate'];
                    $fieldsTemplate = get_class_vars($requestTemplate);
                    $fields = [];

                    foreach ($fieldsTemplate as $field => $value) {
                        $fields[] = $field;
                    }

                    $queryString = '?';
                    $bodyString = '';

                    if ($requestMethod === 'get' or $requestMethod === 'put') {
                        foreach ($fields as $field) {
                            $queryString .= $field . '&';
                        }

                        foreach ($fields as $field) {
                            $bodyString .= "\n  ";
                            $bodyString .= $field . ':';
                        }

                        $queryString = rtrim($queryString, '&');
                    }

                    if ($requestMethod === 'post' or $requestMethod === 'delete') {
                        foreach ($fields as $field) {
                            $bodyString .= "\n  ";
                            $bodyString .= $field . ':';
                        }
                    }

                    $text = "meta {\n  name: {$name}\n  type: http\n  seq: 1\n}\n\n{$requestMethod} {\n  url: {{host}}{$requestUri}{$queryString}\n  body: formUrlEncoded\n  auth: none\n}\n\nbody:form-urlencoded {{$bodyString}\n}\n\nquery {{$bodyString}\n}";

                    file_put_contents($fileName, $text);
                }
            }
        }

        return $this;
    }

    private function getSubclasses(string $parent): array
    {
        $result = [];

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, $parent))
                $result[] = $class;
        }

        return $result;
    }
}
