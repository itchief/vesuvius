<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use LogicException;
use Throwable;

/**
 * Represents a connection between PHP and a database server.
 */
class Connection
{
    protected ?PDO $pdo = null;
    protected string $dsn = '';
    protected ?string $user = null;
    protected ?string $password = null;
    protected array $options = [];
    protected int $transactionDepth = 0;

    public function __construct($config)
    {
        $this->dsn = $this->setDsn($config);
        $this->user = $config['user'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->options = array_replace($config['options'] ?? [], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    protected function setDsn(array $config): string
    {
        extract($config, EXTR_SKIP);

        return isset($port)
            ? "mysql:host=$host;port=$port;dbname=$database;charset=$charset"
            : "mysql:host=$host;dbname=$database;charset=$charset";
    }

    public function reopen(): void
    {
        $this->close();
        $this->open();
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    /**
     * Подключение к базе данных
     */
    public function open(): void
    {
        if ($this->pdo) {
            return;
        }
        $this->pdo = new PDO($this->dsn, $this->user, $this->password, $this->options);
    }

    public function getColumns(string $table): array
    {
        $result = [];
        $rows = $this->select("SHOW COLUMNS FROM $table");
        foreach ($rows as $row) {
            $result[$row['Field']] = [
                'type' => $row['Type'],
                'is_null' => $row['Null'] === 'YES',
                'default' => $row['Default'],
                'key' => $row['Key'],
                'extra' => $row['Extra']
            ];
        }
        return $result;
    }

    public function select(string $queryString, array $bindings = []): array
    {
        $statement = $this->run($queryString, $bindings);
        return $statement->fetchAll();
    }

    public function run(string $queryString, array $params = []): PDOStatement
    {
        $statement = $this->getPdo()->prepare($queryString);
        $statement->execute($params);
        return $statement;
    }

    public function getPdo(): ?PDO
    {
        $this->open();
        return $this->pdo;
    }

    public function quote(string $string): string
    {
        return $this->getPdo()->quote($string);
    }

    /**
     * @throws Throwable
     */
    public function transaction(callable $callback): mixed
    {
        if ($this->transactionDepth === 0) {
            $this->beginTransaction();
        }

        $this->transactionDepth++;
        try {
            $result = $callback($this);
        } catch (Throwable $e) {
            $this->transactionDepth--;
            if ($this->transactionDepth === 0) {
                $this->rollback();
            }
            throw $e;
        }
        $this->transactionDepth--;
        if ($this->transactionDepth === 0) {
            $this->commit();
        }
        return $result;
    }

    public function beginTransaction(): void
    {
        if ($this->transactionDepth !== 0) {
            throw new LogicException(__METHOD__ . '() call is forbidden inside a transaction() callback');
        }
        $this->getPdo()->beginTransaction();
    }

    public function rollBack(): void
    {
        if ($this->transactionDepth !== 0) {
            throw new LogicException(__METHOD__ . '() call is forbidden inside a transaction() callback');
        }
        $this->getPdo()->rollBack();
    }

    public function commit(): void
    {
        if ($this->transactionDepth !== 0) {
            throw new LogicException(__METHOD__ . '() call is forbidden inside a transaction() callback');
        }
        $this->getPdo()->commit();
    }

    public function selectOne($queryString, $bindings = []): ?array
    {
        $statement = $this->run($queryString, $bindings);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function insert(string $queryString, array $bindings = []): bool
    {
        $statement = $this->run($queryString, $bindings);
        return $statement->rowCount() > 0;
    }

    public function insertAndGetId(string $queryString, array $bindings = []): string|int
    {
        $statement = $this->run($queryString, $bindings);
        return $this->getInsertId();
    }

    public function getInsertId(): bool|int|string
    {
        $id = $this->getPdo()->lastInsertId();
        return is_numeric($id) ? (int)$id : $id;
    }

    public function update($queryString, $bindings = []): int
    {
        $statement = $this->run($queryString, $bindings);
        return $statement->rowCount();
    }

    public function delete($queryString, $bindings = []): int
    {
        $statement = $this->run($queryString, $bindings);
        return $statement->rowCount();
    }
}
