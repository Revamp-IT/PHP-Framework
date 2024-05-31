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
    private array $objects;
    private array $cores;
    protected static ?ContainerInterface $instance = null;

    public static function getInstance(): ContainerInterface
    {
        if (is_null(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    public function __construct()
    {
        $this->registerCore(BootstrapInterface::class, Bootstrap::class);
        $this->registerCore(RequestHandlerInterface::class,RequestHandler::class);
        $this->registerCore(ConfigManagerInterface::class, ConfigManager::class);
        $this->registerCore(DataMapHandlerInterface::class, DataMapHandler::class);
        $this->registerCore(UserHandlerInterface::class, UserHandler::class);
        $this->registerCore(HeaderInterface::class, Header::class);
        $this->registerCore(TokenInterface::class, Token::class);
        $this->registerCore(CacheHandlerInterface::class, CacheHandler::class);
        $this->registerCore(JsonErrorInterface::class, JsonError::class);
        $this->registerCore(CookieInterface::class, Cookie::class);

        $this->registerComponents();
    }

    public final function get(string $interface): object
    {
        return $this->objects[$interface] ?? $this->prepareObject($interface);
    }

    public final function registerCore(string $interface, string $class): void
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

    private function prepareObject(string $interface): object
    {
        $class = $this->cores[$interface];
        $reflector = new ReflectionClass($class);

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