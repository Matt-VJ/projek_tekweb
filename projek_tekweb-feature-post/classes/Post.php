<?php

class Post {
    private $db;

    public function __construct($db) {
        $this->db = $db;
}

//CREATE
public function create($title, $excerpt, $content, $category_id = null, $image) {
    $stmt = $this->db->prepare("INSERT INTO topher_posts (title, excerpt, content, category_id, image, published_at)
    VALUES (:title, :excerpt, :content, :category_id, :image, NOW())");

    return $stmt->execute([
            ':title' => $title,
            ':excerpt' => $excerpt,
            ':content' => $content,
            ':category_id' => $category_id,
            ':image' => $image
        ]);
    }

    // READ ALL
    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT posts.*, categories.name AS category_name 
            FROM posts
            LEFT JOIN categories ON posts.category_id = categories.id
            ORDER BY id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ ONE
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function update($id, $title, $excerpt, $content, $category_id, $image = null)
    {
        if ($image === null) {
            $sql = "UPDATE posts SET title=?, excerpt=?, content=?, category_id=? WHERE id=?";
            return $this->db->prepare($sql)->execute([$title, $excerpt, $content, $category_id, $id]);
        } else {
            $sql = "UPDATE posts SET title=?, excerpt=?, content=?, category_id=?, image=? WHERE id=?";
            return $this->db->prepare($sql)->execute([$title, $excerpt, $content, $category_id, $image, $id]);
        }
    }

    // DELETE
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id=?");
        return $stmt->execute([$id]);
    }
}