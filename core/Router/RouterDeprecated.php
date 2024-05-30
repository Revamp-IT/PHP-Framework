<?php

namespace Revamp\Core\Router;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Revamp\Core\Cache\CacheHandlerInterface;
use Revamp\Core\Container\ContainerInterface;
use Revamp\Core\Header\HeaderInterface;
use Revamp\Core\Token\TokenInterface;
use Revamp\Core\Types\Cache\Cache;
use Revamp\Core\Types\Request\Request;
use Revamp\Core\Types\Response\Response;
use Revamp\Core\Types\Route\Route;
use Revamp\Core\Types\Template\Controller\ControllerTemplate;
use Revamp\Core\Types\Template\Controller\ControllerTemplateInterface;
use Revamp\Core\Types\Template\Request\RequestTemplateInterface;
use Revamp\Core\Types\Template\Response\ResponseTemplateInterface;
use Revamp\Core\Types\Token\Authenticate;

final class RouterDeprecated implements RouterInterface
{
    private ControllerTemplateInterface $controller;
    private RequestTemplateInterface $request;
    private ResponseTemplateInterface $response;
    private CacheHandlerInterface $cache;
    private string $controllerName;
    private string $controllerMethod;

    private string $requestUri;
    private string $requestMethod;
    private array $requestBody;

    private array $dependencies = [];

    private array|string $readyResponse;

    public final function __construct(
        private ContainerInterface $container
    )
    {
        $this->cache = $this->container->get(CacheHandlerInterface::class);

        $this
            ->parseRequestData()
            ->getMethod()
            ->authenticate()
            ->getCache()
            ->getRequest()
            ->getResponse()
            ->setRequest()
            ->injectDependencies()
            ->run()
            ->response()
            ->setCache();
    }

    private function parseRequestData(): RouterDeprecated
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];

        header('Access-Control-Allow-Origin: *');

        if ($this->requestMethod == 'OPTIONS') {
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Access-Token, Refresh-Token');
            die();
        }

        $this->requestUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $this->requestBody = json_decode(file_get_contents('php://input'), true);

        return $this;
    }

    private function getMethod(): RouterDeprecated
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

    private function authenticate(): RouterDeprecated
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Authenticate::class);

        if ($attribute) {
            $tokenHandler = $this->container->get(TokenInterface::class);
            $headersHandler = $this->container->get(HeaderInterface::class);

            $accessToken = $headersHandler->getHeader('Access-Token');
            $refreshToken = $headersHandler->getHeader('Refresh-Token');

            if (!$accessToken || !$refreshToken) {
                header("HTTP/1.0 401 Unauthorized");
                die();
            }

            if (!$tokenHandler->validateToken($accessToken)) {
                if (!$tokenHandler->validateToken($refreshToken)) {
                    header("HTTP/1.0 401 Unauthorized");
                    die();
                }

                header("Content-type: application/json");
                $accessToken = $tokenHandler->generateAccessToken($tokenHandler->getPart($accessToken, 2));
                $refreshToken = $tokenHandler->generateRefreshToken($accessToken);
                echo json_encode([
                   'access_token' => $accessToken,
                   'refresh_token' => $refreshToken,
                ]);
            }
        }

        return $this;
    }

    private function getCache(): RouterDeprecated
    {
        $reflector = new ReflectionClass($this->controllerName);
        $method = $reflector->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Cache::class);

        if ($attribute) {
            $uri = str_replace('/', ':', $this->requestUri) . "?" . http_build_query($this->requestBody);

            $data = $this->cache->get($uri);

            if ($data) {
                echo $data;
                die();
            }
        }

        return $this;
    }

    private function getRequest(): RouterDeprecated
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Request::class)[0];
        $request = $attribute->getArguments()['requestTemplate'];

        $this->request = new $request();

        return $this;
    }

    private function getResponse(): RouterDeprecated
    {
        $controller = new ReflectionClass($this->controllerName);
        $method = $controller->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Response::class)[0];
        $response = $attribute->getArguments()['responseTemplate'];

        $this->response = new $response();

        return $this;
    }

    private function setRequest(): RouterDeprecated
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

    private function injectDependencies(): RouterDeprecated
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

    private function run(): RouterDeprecated
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

    private function response(): RouterDeprecated
    {
        $this->readyResponse = get_object_vars($this->response);

        foreach ($this->readyResponse as $field => $value) {
            if (!$value) throw new Exception("Unfilled field {$field}");
        }

        $this->readyResponse = json_encode($this->readyResponse);

        header("Content-type: application/json");

        echo $this->readyResponse;

        return $this;
    }

    private function setCache(): RouterDeprecated
    {
        $reflector = new ReflectionClass($this->controllerName);
        $method = $reflector->getMethod($this->controllerMethod);
        $attribute = $method->getAttributes(Cache::class);

        if ($attribute) {
            $uri = str_replace('/', ':', $this->requestUri) . "?" . http_build_query($this->requestBody);

            $this->cache->set($uri, $this->readyResponse);
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