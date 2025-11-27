<?php

class Comment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all approved comments for a post
    public function getByPostId($post_id, $limit = 50, $offset = 0) {
        $post_id = (int)$post_id;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $stmt = $this->db->prepare("
            SELECT c.*, u.username 
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.post_id = :post_id AND c.status = 'approved'
            ORDER BY c.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get comment count for a post
    public function getCountByPostId($post_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM comments 
            WHERE post_id = ? AND status = 'approved'
        ");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Create a new comment
    public function create($post_id, $content, $author_name = null, $author_email = null, $user_id = null) {
        $stmt = $this->db->prepare("
            INSERT INTO comments (post_id, user_id, author_name, author_email, content, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'approved', NOW())
        ");
        return $stmt->execute([$post_id, $user_id, $author_name, $author_email, $content]);
    }

    // Get all comments (for admin)
    public function getAll($status = null, $limit = 100, $offset = 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        if ($status) {
            $stmt = $this->db->prepare("
                SELECT c.*, p.title as post_title, u.username
                FROM comments c
                LEFT JOIN topher_posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.status = :status
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("
                SELECT c.*, p.title as post_title, u.username
                FROM comments c
                LEFT JOIN topher_posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update comment status
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE comments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    // Delete comment
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
