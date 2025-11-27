<?php
class Database {
    private static $instance = null;
    private $conn;
    
    // Sesuaikan dengan setting XAMPP kamu
    private $host = 'localhost';
    private $db_name = 'mini_cms';
    private $username = 'root';
    private $password = ''; 

    private function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}