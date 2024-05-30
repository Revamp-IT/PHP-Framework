<?php

require __DIR__ . '/vendor/autoload.php';

use Revamp\Core\Container\Container;
use Revamp\Core\Bootstrap\BootstrapInterface;

try {
    Container::getInstance()->get(BootstrapInterface::class)->boot();
} catch (Throwable $exception) {
    echo 'Error: "' . $exception->getMessage() . '" in ' . $exception->getFile() . ':' . $exception->getLine();
}