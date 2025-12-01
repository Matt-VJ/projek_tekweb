<?php
require_once 'Database.php';

class Comment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($post_id, $content, $author_name) {
        try {
            $sql = "INSERT INTO comments (post_id, author_name, content, created_at) VALUES (:pid, :author, :content, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':pid' => $post_id, ':author' => $author_name, ':content' => $content]);
        } catch (PDOException $e) { return false; }
    }

    public function getByPostId($post_id) {
        $sql = "SELECT * FROM comments WHERE post_id = :pid ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountByPostId($post_id) {
        $sql = "SELECT COUNT(*) as total FROM comments WHERE post_id = :pid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $post_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE comments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll($status = null, $role = 'admin', $user_id = 0) {
        $sql = "SELECT c.*, p.title as post_title, u.username, p.user_id as post_author_id
                FROM comments c
                LEFT JOIN topher_posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE 1=1";
        
        if ($status) {
            $sql .= " AND c.status = :status";
        }

        if ($role !== 'admin') {
            $sql .= " AND p.user_id = :current_uid";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);

        if ($status) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        if ($role !== 'admin') {
            $stmt->bindValue(':current_uid', $user_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>