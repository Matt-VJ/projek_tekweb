<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    // --- FUNGSI REGISTRASI ---
    public function register($username, $password) {
        $username = $this->sanitize($username);

        // Cek username kembar
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() > 0) {
            return "Username sudah dipakai.";
        }

        // Hash Password & Insert sebagai 'editor'
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, role) VALUES (:username, :pass, 'editor')";
        
        try {
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute([':username' => $username, ':pass' => $hash])) {
                return true;
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
        return false;
    }

    // --- FUNGSI LOGIN (STRICT - TANPA COUNTER GAGAL) ---
    public function login($username, $password) {
        $username = $this->sanitize($username);
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Cek User Ada && Username PERSIS SAMA (Case Sensitive) && Password Cocok
        if ($user && $user['username'] === $username && password_verify($password, $user['password'])) {
            // Set Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            return true;
        }
        
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: Login.php");
        exit;
    }

    public function requireLogin() {
        if (!isset($_SESSION['is_logged_in'])) {
            header("Location: ../Login.php");
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            die("Akses Ditolak: Halaman ini khusus Admin.");
        }
    }

    public function getUser() {
        return $_SESSION ?? null;
    }
}