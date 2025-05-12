<?php

class Config {
    private static $instance = null;
    private $dbConfig = [];

    private function __construct() {
        $this->dbConfig = [
            'host' => '127.0.0.1:3306',
            'dbname' => 'raspi',
            'username' => 'raspiVer',
            'password' => 'Verwaltung2025',
            'charset' => 'utf8mb4'
        ];
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
        return "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']};charset={$this->dbConfig['charset']}";
    }
} 