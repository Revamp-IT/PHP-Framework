<?php

namespace Revamp\Core\Bootstrap;

use ReflectionClass;
use ReflectionMethod;
use Revamp\Core\Cache\CacheHandlerInterface;
use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\Container\Container;
use Revamp\Core\Cookie\CookieInterface;
use Revamp\Core\Header\HeaderInterface;
use Revamp\Core\JsonError\JsonErrorInterface;
use Revamp\Core\RequestHandler\RequestHandlerInterface;
use Revamp\Core\Token\TokenInterface;
use Revamp\Core\Types\Cache\Cache;
use Revamp\Core\Types\Request\Request;
use Revamp\Core\Types\Response\Response;
use Revamp\Core\Types\Route\Route;
use Revamp\Core\Types\Template\Controller\ControllerTemplate;
use Revamp\Core\Types\Template\Request\RequestTemplateInterface;
use Revamp\Core\Types\Template\Response\ResponseTemplateInterface;
use Revamp\Core\Types\Token\Authenticate;
use stdClass;

class Bootstrap implements BootstrapInterface
{
    private string $controllerName;
    private string $controllerMethod;

    private ReflectionClass $reflector;
    private ReflectionMethod $reflectorMethod;

    private stdClass $uriParams;
    private string $requestTemplate;
    private string $responseTemplate;

    private array $actionDependencies = [];

    private RequestTemplateInterface $filledRequestTemplate;
    private ResponseTemplateInterface $filledResponseTemplate;

    public function __construct(
        private RequestHandlerInterface $requestHandler,
        private ConfigManagerInterface $configManager,
        private TokenInterface $tokenHandler,
        private CookieInterface $cookieHandler,
        private CacheHandlerInterface $cacheHandler,
        private HeaderInterface $headerHandler,
        private JsonErrorInterface $error,
    ) {}

    public function boot(): void
    {
        $response = $this->requestHandler->getMethod() != 'OPTIONS' ? $this->work() : $this->responseOptions();

        foreach ($this->headerHandler->getResponseHeaders() as $header) header($header);

        echo $response;
    }

    private function matchUri(string $pattern, string $uri): bool
    {
        if (!str_ends_with($pattern, '/')) $pattern .= '/';

        $patternParts = explode('/', $pattern);
        $uriParts = explode('/', $uri);

        if (count($patternParts) != count($uriParts)) return false;

        $index = 0;
        foreach ($patternParts as $part){
            if (str_starts_with($part, '{')) {
                unset($patternParts[$index]);
                unset($uriParts[$index]);
            }

            $index++;
        }

        if (count(array_diff($patternParts, $uriParts)) != 0) return false;

        return true;
    }

    private function work(): string
    {
        $this->headerHandler->setResponseHeader('Content-Type: application/json');
        $this->headerHandler->setResponseHeader('Access-Control-Allow-Origin: ' . $this->configManager->get('FRONTEND_URL'));

        $this->getAction()->authenticate();

        $cache = $this->getCache();
        if (is_string($cache)) return $cache;

        $this->getActionTemplates()->collectUriParams()->validateRequestBody()->prepareActionDependencies()->runAction()->validateResponseBody();

        $data = $this->makeResponse();

        $this->setCache();

        return $data;
    }

    private function responseOptions(): string
    {
        $this->headerHandler->setResponseHeader('Access-Control-Allow-Origin: ' . $this->configManager->get('FRONTEND_URL'));
        $this->headerHandler->setResponseHeader('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        $this->headerHandler->setResponseHeader('Access-Control-Allow-Headers: Content-Type');

        return "";
    }

    private function getAction()
    {
        $controllers = $this->getControllers();

        foreach ($controllers as $controller) {
            $reflector = new ReflectionClass($controller);
            $methods = $reflector->getMethods();

            foreach ($methods as $method) {
                $attribute = $method->getAttributes(Route::class);

                if ($attribute) {
                    $attribute = $attribute[0];

                    $args = $attribute->getArguments();

                    if ($this->matchUri($args['uri'], $this->requestHandler->getUri()) and in_array($this->requestHandler->getMethod(), $args['methods'])) {
                        $this->requestHandler->setParams($args['uri']);

                        $this->controllerName = $controller;
                        $this->reflector = $reflector;

                        $this->controllerMethod = $method->getName();
                        $this->reflectorMethod = $reflector->getMethod($method->getName());

                        return $this;
                    }
                }
            }
        }

        $this->error->throw(R1);
    }

    private function authenticate(): Bootstrap
    {
        $attribute = $this->reflectorMethod->getAttributes(Authenticate::class);

        if ($attribute) {
            $accessToken = $this->cookieHandler->get('Access-Token');
            $refreshToken = $this->cookieHandler->get('Refresh-Token');

            if (!$accessToken || !$refreshToken) {
                $this->error->throw(A1);
            }

            if (!$this->tokenHandler->validateToken($accessToken)) {
                if (!$this->tokenHandler->validateToken($refreshToken)) {
                    $this->error->throw(A2);
                }

                $accessToken = $this->tokenHandler->generateAccessToken($this->tokenHandler->getPart($accessToken, 2));
                $refreshToken = $this->tokenHandler->generateRefreshToken($accessToken);

                $this->cookieHandler->set('Access-Token', $accessToken);
                $this->cookieHandler->set('Refresh-Token', $refreshToken);
            }
        }

        return $this;
    }

    private function getCache(): Bootstrap|string
    {
        $attribute = $this->reflectorMethod->getAttributes(Cache::class);

        if ($attribute) {
            $uri = $this->cacheHandler->buildKey(
                $this->requestHandler->getUri(),
                $this->requestHandler->getParams()
            );

            $data = $this->cacheHandler->get($uri);

            if ($data) {
                return $data;
            }
        }

        return $this;
    }

    private function getActionTemplates(): Bootstrap
    {
        $attribute = $this->reflectorMethod->getAttributes(Request::class)[0];
        $this->requestTemplate = $attribute->getArguments()['requestTemplate'];

        $attribute = $this->reflectorMethod->getAttributes(Response::class)[0];
        $this->responseTemplate = $attribute->getArguments()['responseTemplate'];

        return $this;
    }

    private function collectUriParams(): Bootstrap
    {
        $params = new stdClass();
        foreach ($this->requestHandler->getParams() as $field => $value) $params->$field = $value;

        $this->uriParams = $params;

        return $this;
    }

    private function validateRequestBody(): Bootstrap
    {
        $template = new $this->requestTemplate;

        foreach ($this->requestHandler->getBody() as $field => $value) $template->$field = $value;

        foreach (get_object_vars($template) as $value) {
            if ($value === null) $this->error->throw(R2);
        }

        $this->filledRequestTemplate = $template;

        return $this;
    }

    private function prepareActionDependencies(): Bootstrap
    {
        $args = $this->reflectorMethod->getParameters();

        foreach ($args as $arg) {
            $interface = $arg->getType()->getName();
            $name = $arg->getName();

            $this->actionDependencies[$name] = Container::getInstance()->get($interface);
        }

        return $this;
    }

    private function runAction(): Bootstrap
    {
        $method = $this->controllerMethod;

        $controller = new $this->controllerName(
            $this->uriParams,
            $this->filledRequestTemplate,
            new $this->responseTemplate
        );
        $controller->$method(...$this->actionDependencies);
        $this->filledResponseTemplate = $controller->getResponse();

        return $this;
    }

    private function validateResponseBody(): Bootstrap
    {
        foreach (get_object_vars($this->filledResponseTemplate) as $value) {
            if ($value === null) $this->error->throw(R3);
        }

        return $this;
    }

    private function makeResponse(): string
    {
        return json_encode(get_object_vars($this->filledResponseTemplate));
    }

    private function setCache(): void
    {
        $attribute = $this->reflectorMethod->getAttributes(Cache::class);

        if ($attribute) {
            $uri = $this->cacheHandler->buildKey(
                $this->requestHandler->getUri(),
                $this->requestHandler->getParams()
            );

            $this->cacheHandler->set($uri, json_encode(get_object_vars($this->filledResponseTemplate)));
        }
    }

    private function getControllers(): array
    {
        $result = [];

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, ControllerTemplate::class))
                $result[] = $class;
        }

        return $result;
    }
}