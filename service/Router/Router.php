<?php

namespace Revamp\Service\Router;

use Revamp\Service\Container\ContainerInterface;
use Revamp\Service\Header\HeaderInterface;
use Revamp\Service\Token\TokenInterface;
use Revamp\Service\Types\Controller\ControllerTemplateInterface;
use Revamp\Service\Types\Request\Request;
use Revamp\Service\Types\Request\RequestTemplateInterface;
use Revamp\Service\Types\Response\Response;
use Revamp\Service\Types\Response\ResponseTemplateInterface;
use Revamp\Service\Types\Route\Route;
use ReflectionClass;
use ReflectionProperty;

use Revamp\Service\Types\Controller\ControllerTemplate;
use Revamp\Service\Types\Token\Authenticate;

final class Router implements RouterInterface
{
    private ControllerTemplateInterface $controller;
    private RequestTemplateInterface $request;
    private ResponseTemplateInterface $response;
    private string $controllerName;
    private string $controllerMethod;

    private string $requestUri;
    private string $requestMethod;
    private array $requestBody;

    private array $dependencies = [];

    public final function __construct(
        private ContainerInterface $container
    )
    {
        $this
            ->parseRequestData()
            ->getMethod()
            ->authenticate()
            ->getRequest()
            ->getResponse()
            ->setRequest()
            ->injectDependencies()
            ->run()
            ->response();
    }

    private function parseRequestData(): Router
    {
        $this->requestUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestBody = $_GET ?? $_POST;

        return $this;
    }

    private function getMethod(): Router
    {
        $controllers = $this->getSubclasses(ControllerTemplate::class);

        foreach ($controllers as $controller) {
            $class = new ReflectionClass($controller);
            $methods = $class->getMethods();

            foreach ($methods as $method) {
                $attribute = $method->getAttributes(Route::class);

                if ($attribute) {
                    $attribute = $attribute[0];

                    $args = $attribute->getArguments();

                    if ($args['uri'] == $this->requestUri and in_array($this->requestMethod, $args['methods'])) {
                        $this->controllerName = $controller;
                        $this->controllerMethod = $method->getName();

                        return $this;
                    }
                }
            }
        }

        header("HTTP/1.0 404 Not Found");
        die();
    }

    private function authenticate(): Router
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Authenticate::class);

        if ($attribute) {
            $tokenHandler = $this->container->get(TokenInterface::class);

            if (!$tokenHandler->validateToken()) {
                header("HTTP/1.0 401 Unauthorized");
                die();
            }
        }

        return $this;
    }

    private function getRequest(): Router
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Request::class)[0];
        $request = $attribute->getArguments()['requestTemplate'];

        $this->request = new $request();

        return $this;
    }

    private function getResponse(): Router
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Response::class)[0];
        $response = $attribute->getArguments()['responseTemplate'];

        $this->response = new $response();

        return $this;
    }

    private function setRequest(): Router
    {

        $template = new ReflectionClass($this->request);
        $templateVars = $template->getProperties(ReflectionProperty::IS_PUBLIC);

        $templateFields = [];
        foreach ($templateVars as $templateVar) {
            $templateFields[] = $templateVar->getName();
        }

        $requestFields = [];
        foreach ($this->requestBody as $field => $value) $requestFields[] = $field;

        if (count(array_diff($templateFields, $requestFields)) != 0) {
            header("HTTP/1.0 400 Bad Request");
            die();
        }

        foreach ($this->requestBody as $field => $value) {
            $this->request->$field = $value;
        }

        return $this;
    }

    private function injectDependencies(): Router
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $args = $method->getParameters();

        foreach ($args as $arg) {
            $interface = $arg->getType()->getName();
            $name = $arg->getName();

            $this->dependencies[$name] = $this->container->get($interface);
        }

        return $this;
    }

    private function run(): Router
    {
        $method = $this->controllerMethod;

        $this->controller = new $this->controllerName(
            $this->request,
            $this->response,
        );
        $this->controller->$method(...$this->dependencies);
        $this->response = $this->controller->getResponse();

        return $this;
    }

    private function response(): void
    {
        $fields = get_object_vars($this->response);
        foreach ($fields as $field => $value) {
            if ($value === null) throw new Exception("Unfilled field {$field}");
        }

        echo json_encode($fields);
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