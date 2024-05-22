<?php

namespace Revamp\Bin\Archi;

require __DIR__ . '/../vendor/autoload.php';

use Revamp\Service\Bruno\BrunoHandler;
use Revamp\Service\Doc\Doc;

$command = $argv[1];

$args = [
    'doc' => Doc::class,
    'bruno' => BrunoHandler::class,
];

new $args[$command]();