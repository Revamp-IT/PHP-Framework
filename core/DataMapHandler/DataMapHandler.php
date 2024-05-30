<?php

namespace Revamp\Core\DataMapHandler;

use Exception;
use PDO;
use ReflectionClass;
use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\Types\DataMap\Required;
use Revamp\Core\Types\DataMap\Unique;
use Revamp\Core\Types\Template\DataMap\DataMapTemplateInterface;

final class DataMapHandler implements DataMapHandlerInterface
{
    private PDO $connection;
    private DataMapTemplateInterface $map;
    private string $table;

    public final function __construct(
        private ConfigManagerInterface $config
    )
    {
        $this->connect();
    }

    private function generateDsn(): string
    {
        $host = $this->config->get('DATABASE_HOST');
        $port = $this->config->get('DATABASE_PORT');
        $dbname = $this->config->get('DATABASE_NAME');
        $user = $this->config->get('DATABASE_USER');
        $password = $this->config->get('DATABASE_PASS');

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
        $this->table = strtolower((new ReflectionClass($this->map))->getShortName());

        return $this;
    }

    private function buildQuery(array $query): string
    {
        $str = '';

        foreach ($query as $key => $value) {
            $str .= strtoupper($key) . " = '" . $value . "' AND ";
        }

        return substr($str, 0, -5);
    }

    private function buildColumns(array $data): string
    {
        $columns = '';

        foreach ($data as $key => $value) {
            $columns .= '"' . $key . '", ';
        }

        return substr($columns, 0, -2);
    }

    private function buildValues(array $data): string
    {
        $values = '';

        foreach ($data as $key => $value) {
            $values .= "'" . $value . "', ";
        }

        return substr($values, 0, -2);
    }

    private function fillMap(array $data): array
    {
        $mapReflector = new ReflectionClass($this->map);
        $properties = $mapReflector->getProperties();

        $allProperties = [];
        $requiredProperties = [];
        $filledProperties = [];

        foreach ($properties as $property) {
            $attributes = $property->getAttributes(Required::class);

            if (!empty($attributes)) {
                $requiredProperties[] = $property->getName();
            }
        }

        foreach ($properties as $property) {
            $allProperties[] = $property->getName();
        }

        foreach ($requiredProperties as $property) {
            if (!isset($data[$property])) throw new Exception("Unfilled required field '{$property}'");
        }

        foreach ($data as $key => $value) {
            if (!in_array($key, $allProperties)) throw new Exception("Unknown field '{$key}'");
        }

        foreach ($data as $key => $value) {
            $filledProperties[$key] = $value;
        }

        return $filledProperties;
    }

    private function isRepeated(array $data): bool
    {
        $mapReflector = new ReflectionClass($this->map);
        $properties = $mapReflector->getProperties();

        $uniqueProperties = [];

        foreach ($properties as $property) {
            $attributes = $property->getAttributes(Unique::class);
            if (!empty($attributes)) {
                $uniqueProperties[$property->getName()] = $data[$property->getName()];
            }
        }

        return !empty($this->getBy($uniqueProperties));
    }

    public final function getById(int $id): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM \"{$this->table}\" WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: [];
    }

    public final function getBy(array $query, bool $many = true): array
    {
        $query = $this->buildQuery($query);

        $stmt = $this->connection->prepare("SELECT * FROM \"{$this->table}\" WHERE {$query}");
        $stmt->execute();

        $res = $many ? $stmt->fetchAll() : $stmt->fetch();

        return $res ?: [];
    }

    public final function insert(array $data): bool
    {
        $data = $this->fillMap($data);
        $columns = $this->buildColumns($data);
        $values = $this->buildValues($data);

        if ($this->isRepeated($data)) throw new Exception("Repeated data in unique columns");

        $stmt = $this->connection->prepare("INSERT INTO \"{$this->table}\" ({$columns}) VALUES ({$values})");

        return $stmt->execute();
    }
}