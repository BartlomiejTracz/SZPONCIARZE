<?php
// Konfiguracja bazy danych SQLite

class Database {
    private $db_file = __DIR__ . '/../database/filmweb.sqlite';
    private $conn = null;

    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO('sqlite:' . $this->db_file);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }
}