<?php
require_once 'Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT * FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createUser($username, $password, $role) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() > 0) {
            return "Username sudah ada.";
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (username, password, role) VALUES (:username, :pass, :role)";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute([':username' => $username, ':pass' => $hash, ':role' => $role])) {
                return true;
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
        return false;
    }

    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>