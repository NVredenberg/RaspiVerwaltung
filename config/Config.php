<?php
declare(strict_types=1);

class Config {
    private static $instance = null;
    private $dbConfig = [];

    private function __construct() {
        $this->dbConfig = [
            'host' => $this->env('DB_HOST', '127.0.0.1'),
            'port' => $this->env('DB_PORT', '3306'),
            'dbname' => $this->env('DB_NAME', 'raspi'),
            'username' => $this->env('DB_USER', 'raspiVer'),
            'password' => $this->env('DB_PASSWORD', ''),
            'charset' => $this->env('DB_CHARSET', 'utf8mb4')
        ];
    }

    private function env(string $key, string $default = ''): string {
        $value = getenv($key);
        if ($value === false || $value === '') {
            $value = $_ENV[$key] ?? $default;
        }
        return (string)$value;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDbConfig() {
        return $this->dbConfig;
    }

    public function getDsn() {
        return "mysql:host={$this->dbConfig['host']};port={$this->dbConfig['port']};dbname={$this->dbConfig['dbname']};charset={$this->dbConfig['charset']}";
    }
}
