<?php
require_once 'Database.php';

class Post {
    private $db;
    private $table_post = "topher_posts"; 
    private $table_category = "topher_categories";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($title, $excerpt, $content, $category_id, $image, $user_id) {
        $category_id = empty($category_id) ? null : $category_id;
        
        $sql = "INSERT INTO " . $this->table_post . " (title, excerpt, content, category_id, image, user_id, published_at) 
                VALUES (:title, :excerpt, :content, :cat, :img, :uid, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':excerpt' => $excerpt,
            ':content' => $content,
            ':cat' => $category_id,
            ':img' => $image,
            ':uid' => $user_id
        ]);
    }

    public function update($id, $title, $excerpt, $content, $category_id, $image) {
        $category_id = empty($category_id) ? null : $category_id;
        $sql = "UPDATE " . $this->table_post . " SET title=:title, excerpt=:excerpt, content=:content, category_id=:cat, image=:img WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':title' => $title, ':excerpt' => $excerpt, ':content' => $content, ':cat' => $category_id, ':img' => $image, ':id' => $id]);
    }

    public function delete($id) {
        try {
            $stmtComm = $this->db->prepare("DELETE FROM comments WHERE post_id = :id");
            $stmtComm->execute([':id' => $id]);
            $stmtPost = $this->db->prepare("DELETE FROM " . $this->table_post . " WHERE id = :id");
            return $stmtPost->execute([':id' => $id]);
        } catch (PDOException $e) { return false; }
    }

    public function getAll($role = 'admin', $user_id = 0) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM " . $this->table_post . " p 
                LEFT JOIN " . $this->table_category . " c ON p.category_id = c.id";
        
        if ($role !== 'admin') {
            $sql .= " WHERE p.user_id = :uid";
        }

        $sql .= " ORDER BY p.published_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($role !== 'admin') {
            $stmt->execute([':uid' => $user_id]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }
}
?>