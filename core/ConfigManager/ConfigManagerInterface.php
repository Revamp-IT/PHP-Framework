<?php

namespace Revamp\Core\ConfigManager;

interface ConfigManagerInterface
{
    public function __construct();
    public function get($name): string;
}