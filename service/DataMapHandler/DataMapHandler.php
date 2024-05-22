<?php

namespace Revamp\Service\DataMapHandler;

use Revamp\Service\ConfigManager\ConfigManagerInterface;

use Revamp\Service\Types\DataMap\DataMapInterface;

use ReflectionClass;
use PDO;

final class DataMapHandler implements DataMapHandlerInterface
{
    private PDO $connection;
    private DataMapInterface $map;
    private string $table;

    public final function __construct(
        private ConfigManagerInterface $config
    )
    {
        $this->connect();
    }

    private function generateDsn(): string
    {
        $host = $this->config->getDatabaseHost();
        $port = $this->config->getDatabasePort();
        $dbname = $this->config->getDatabaseName();
        $user = $this->config->getDatabaseUser();
        $password = $this->config->getDatabasePassword();

        return "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";
    }

    private function connect(): void
    {
        $dsn = $this->generateDsn();

        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new PDO($dsn, options: $opt);
    }

    public function setMap(string $map): DataMapHandler
    {
        $this->map = new $map();
        $this->table = (new ReflectionClass($this->map))->getShortName();

        return $this;
    }

    private function fillMap(): void
    {

    }

    private function buildQuery(array $query): string
    {
        $str = '';

        foreach ($query as $key => $value) {
            $str .= strtoupper($key) . " = '" . $value . "' AND ";
        }

        return substr($str, 0, -5);
    }

    public final function getById(int $id): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: [];
    }

    public final function getBy(array $query): array
    {
        $query = $this->buildQuery($query);

        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE {$query}");
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public final function insert(): void
    {

    }
}