<?php

require __DIR__ . '/vendor/autoload.php';

use Revamp\Service\Router\Router;
use Revamp\Service\Container\Container;

try {
    $router = new Router(new Container());
} catch (Throwable $exception) {
    echo 'Error: "' . $exception->getMessage() . '" in ' . $exception->getFile() . ':' . $exception->getLine();
}