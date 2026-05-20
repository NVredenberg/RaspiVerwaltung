<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = Config::getInstance();
  
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $config->getDsn(),
                $config->getDbConfig()['username'],
                $config->getDbConfig()['password'],
                $options
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Datenbankverbindung fehlgeschlagen.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Datenbankabfrage fehlgeschlagen.");
        }
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data) {
        $table = $this->identifier($table);
        $fields = array_keys($data);
        $quotedFields = array_map([$this, 'identifier'], $fields);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $quotedFields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $table = $this->identifier($table);
        $fields = array_map(function($field) {
            return $this->identifier($field) . " = ?";
        }, array_keys($data));
        
        $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = []) {
        $table = $this->identifier($table);
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }

    public function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }

    private function identifier(string $identifier): string {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException('Ungültiger Datenbankbezeichner.');
        }
        return "`{$identifier}`";
    }
}
