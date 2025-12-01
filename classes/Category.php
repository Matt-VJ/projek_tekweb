<?php
require_once 'Database.php';

class Category {
    private $db;
    private $table = "topher_categories";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM " . $this->table . " ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name) {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (name, slug) VALUES (:name, :slug)");
            return $stmt->execute([':name' => $name, ':slug' => $slug]);
        } catch (PDOException $e) {
            return false;
        }
    }


    public function delete($id) {
        try {
            $updatePosts = $this->db->prepare("UPDATE topher_posts SET category_id = NULL WHERE category_id = :id");
            $updatePosts->execute([':id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>