<?php

namespace Revamp\Service\ConfigManager;

interface ConfigManagerInterface
{
    public function __construct();
    public function getDatabaseHost(): string;
    public function getDatabasePort(): string;
    public function getDatabaseName(): string;
    public function getDatabaseUser(): string;
    public function getDatabasePassword(): string;
    public function getSecret(): string;
}