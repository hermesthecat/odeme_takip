<?php
class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct()
    {
        try {
            // Load and validate config
            $this->loadConfig();

            // Build DSN
            $dsn = $this->buildDsn();

            // Create connection
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

            // Set error mode after connection
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Database connection failed:\nError: %s\nDSN: %s\nUser: %s\n",
                $e->getMessage(),
                $dsn ?? 'DSN not built',
                $this->config['username'] ?? 'username not set'
            ));
        } catch (Exception $e) {
            throw new Exception("Configuration error: " . $e->getMessage());
        }
    }

    private function loadConfig(): void
    {
        $configFile = __DIR__ . '/config.php';

        if (!file_exists($configFile)) {
            throw new Exception("Config file not found at: $configFile");
        }

        $this->config = require $configFile;

        // Validate required config settings
        $required = ['host', 'dbname', 'username', 'password', 'charset'];
        $missing = array_diff($required, array_keys($this->config));

        if (!empty($missing)) {
            throw new Exception(
                "Missing required configuration parameters: " .
                    implode(', ', $missing)
            );
        }

        // Set default options if not provided
        if (!isset($this->config['options'])) {
            $this->config['options'] = [];
        }

        // Ensure critical PDO options are set
        $this->config['options'] += [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
    }

    private function buildDsn(): string
    {
        // Validate host and database name
        if (empty($this->config['host'])) {
            throw new Exception("Database host not specified");
        }
        if (empty($this->config['dbname'])) {
            throw new Exception("Database name not specified");
        }

        // Build DSN string
        return sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            $this->config['host'],
            $this->config['dbname'],
            $this->config['charset'] ?? 'utf8mb4'
        );
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // Helper method for executing SELECT queries
    public function select(string $query, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Select query failed:\nError: %s\nQuery: %s\nParams: %s",
                $e->getMessage(),
                $query,
                print_r($params, true)
            ));
        }
    }

    // Helper method for executing INSERT queries
    public function insert(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return (int)$this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Insert query failed:\nError: %s\nQuery: %s\nParams: %s",
                $e->getMessage(),
                $query,
                print_r($params, true)
            ));
        }
    }

    // Helper method for INSERT ... ON DUPLICATE KEY UPDATE queries
    public function insertOrUpdate(string $table, array $data, array $updateColumns = null): int
    {
        try {
            $columns = array_keys($data);
            $values = array_map(fn($col) => ":$col", $columns);

            $query = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $table,
                implode(', ', $columns),
                implode(', ', $values)
            );

            // If updateColumns is not provided, update all columns
            if ($updateColumns === null) {
                $updateColumns = $columns;
            }

            // Add ON DUPLICATE KEY UPDATE clause
            if (!empty($updateColumns)) {
                $updates = array_map(function ($col) {
                    return "$col = VALUES($col)";
                }, $updateColumns);

                $query .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
            }

            $stmt = $this->connection->prepare($query);
            $stmt->execute($data);

            return (int)$this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Insert or update query failed:\nError: %s\nQuery: %s\nParams: %s",
                $e->getMessage(),
                $query ?? 'Query not built',
                print_r($data, true)
            ));
        }
    }

    // Helper method for executing UPDATE queries
    public function update(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Update query failed:\nError: %s\nQuery: %s\nParams: %s",
                $e->getMessage(),
                $query,
                print_r($params, true)
            ));
        }
    }

    // Helper method for executing DELETE queries
    public function delete(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Delete query failed:\nError: %s\nQuery: %s\nParams: %s",
                $e->getMessage(),
                $query,
                print_r($params, true)
            ));
        }
    }

    // Execute a raw query
    public function exec(string $query): bool
    {
        try {
            return $this->connection->exec($query) !== false;
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Query execution failed:\nError: %s\nQuery: %s",
                $e->getMessage(),
                $query
            ));
        }
    }

    // Execute a query and return PDOStatement
    public function query(string $query): PDOStatement
    {
        try {
            $stmt = $this->connection->query($query);
            if ($stmt === false) {
                throw new Exception("Query execution failed");
            }
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception(sprintf(
                "Query execution failed:\nError: %s\nQuery: %s",
                $e->getMessage(),
                $query
            ));
        }
    }

    // Begin a transaction
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    // Commit a transaction
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    // Rollback a transaction
    public function rollback(): bool
    {
        if ($this->connection->inTransaction()) {
            return $this->connection->rollBack();
        }
        return false;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
