<?php

namespace Revamp\Core\Container;

use Revamp\Core\Bootstrap\Bootstrap;
use Revamp\Core\Bootstrap\BootstrapInterface;
use Revamp\Core\Cache\CacheHandler;
use Revamp\Core\Cache\CacheHandlerInterface;
use Revamp\Core\ConfigManager\ConfigManager;
use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\Cookie\Cookie;
use Revamp\Core\Cookie\CookieInterface;
use Revamp\Core\DataMapHandler\DataMapHandler;
use Revamp\Core\DataMapHandler\DataMapHandlerInterface;
use Revamp\Core\Header\Header;
use Revamp\Core\Header\HeaderInterface;
use Revamp\Core\JsonError\JsonError;
use Revamp\Core\JsonError\JsonErrorInterface;
use Revamp\Core\RequestHandler\RequestHandler;
use Revamp\Core\RequestHandler\RequestHandlerInterface;
use Revamp\Core\Router\RouterDeprecated;
use Revamp\Core\Router\RouterInterface;

use Revamp\Core\Token\Token;
use Revamp\Core\Token\TokenInterface;
use Revamp\Core\User\UserHandler;
use Revamp\Core\User\UserHandlerInterface;
use ReflectionClass;

final class Container implements ContainerInterface
{
    private array $objects = [];
    private array $cores;
    protected static ?ContainerInterface $instance = null;

    public static function getInstance(): ContainerInterface
    {
        if (is_null(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    public function __construct()
    {
        $this->register(BootstrapInterface::class, Bootstrap::class);
        $this->register(RequestHandlerInterface::class,RequestHandler::class);
        $this->register(ConfigManagerInterface::class, ConfigManager::class);
        $this->register(DataMapHandlerInterface::class, DataMapHandler::class);
        $this->register(UserHandlerInterface::class, UserHandler::class);
        $this->register(HeaderInterface::class, Header::class);
        $this->register(TokenInterface::class, Token::class);
        $this->register(CacheHandlerInterface::class, CacheHandler::class);
        $this->register(JsonErrorInterface::class, JsonError::class);
        $this->register(CookieInterface::class, Cookie::class);

        $this->registerComponents();
    }

    public final function get(string $interfaceOrClass): object
    {
        return $this->objects[$interfaceOrClass] ?? $this->prepareObject($interfaceOrClass);
    }

    public final function register(string $interface, string $class): void
    {
        $this->cores[$interface] = $class;
    }

    private function registerComponents(): void
    {
        $controllerFiles = array_diff(scandir(__DIR__ . '/../../src/Controller/'), array('..', '.'));
        foreach ($controllerFiles as $controllerFile) {
            include __DIR__ . '/../../src/Controller/' . $controllerFile;
        }
    }

    private function prepareObject(string $interfaceOrClass): object
    {
        $reflector = new ReflectionClass($interfaceOrClass);

        if ($reflector->isInterface()) {
            $class = $this->cores[$interfaceOrClass];
            $reflector = new ReflectionClass($class);
        } else {
            $class = $interfaceOrClass;
        }

        $constructReflector = $reflector->getConstructor();
        if (empty($constructReflector)) {
            return new $class;
        }

        $constructArguments = $constructReflector->getParameters();
        if (empty($constructArguments)) {
            return new $class;
        }

        $args = [];
        foreach ($constructArguments as $constructArgument) {
            $argumentType = $constructArgument->getType()->getName();
            $args[$constructArgument->getName()] = $this->get($argumentType);
        }

        $object = new $class(...$args);

        $this->objects[$class] = $object;

        return $object;
    }
}