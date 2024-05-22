<?php

namespace Revamp\Service\Container;

use Revamp\Service\ConfigManager\ConfigManager;
use Revamp\Service\ConfigManager\ConfigManagerInterface;
use Revamp\Service\DataMapHandler\DataMapHandler;
use Revamp\Service\DataMapHandler\DataMapHandlerInterface;
use Revamp\Service\Header\Header;
use Revamp\Service\Header\HeaderInterface;
use Revamp\Service\Router\Router;
use Revamp\Service\Router\RouterInterface;

use Revamp\Service\Token\Token;
use Revamp\Service\Token\TokenInterface;
use Revamp\Service\User\User;
use Revamp\Service\User\UserInterface;
use ReflectionClass;

final class Container implements ContainerInterface
{
    private array $objects;
    private array $services;

    public final function __construct()
    {
        $this->registerService(RouterInterface::class, Router::class);
        $this->registerService(ConfigManagerInterface::class, ConfigManager::class);
        $this->registerService(DataMapHandlerInterface::class, DataMapHandler::class);
        $this->registerService(UserInterface::class, User::class);
        $this->registerService(HeaderInterface::class, Header::class);
        $this->registerService(TokenInterface::class, Token::class);

        $this->registerComponents();
    }

    public final function get(string $interface): object
    {
        return $this->objects[$interface] ?? $this->prepareObject($interface);
    }

    public final function registerService(string $interface, string $class): void
    {
        $this->services[$interface] = $class;
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
        $class = $this->services[$interface];
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