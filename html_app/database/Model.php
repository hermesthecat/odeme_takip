<?php
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Find record by ID
    public function find(int $id): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $result = $this->db->select($query, ['id' => $id]);
        return $result ? $result[0] : null;
    }

    // Get all records
    public function all(): array
    {
        $query = "SELECT * FROM {$this->table}";
        return $this->db->select($query);
    }

    // Create new record
    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":$field", $fields);

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        return $this->db->insert($query, $data);
    }

    // Update record
    public function update(int $id, array $data): int
    {
        $fields = array_map(fn($field) => "$field = :$field", array_keys($data));
        $data['id'] = $id;

        $query = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $fields),
            $this->primaryKey
        );

        return $this->db->update($query, $data);
    }

    // Delete record
    public function delete(int $id): int
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->delete($query, ['id' => $id]);
    }

    // Find records by field value
    public function where(string $field, $value): array
    {
        $query = "SELECT * FROM {$this->table} WHERE $field = :value";
        return $this->db->select($query, ['value' => $value]);
    }

    // Find records by multiple conditions
    public function whereAnd(array $conditions): array
    {
        $where = array_map(fn($field) => "$field = :$field", array_keys($conditions));

        $query = sprintf(
            "SELECT * FROM %s WHERE %s",
            $this->table,
            implode(' AND ', $where)
        );

        return $this->db->select($query, $conditions);
    }

    // Count records
    public function count(): int
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->select($query);
        return (int)$result[0]['count'];
    }

    // Begin transaction
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    // Commit transaction
    public function commit(): bool
    {
        return $this->db->commit();
    }

    // Rollback transaction
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
