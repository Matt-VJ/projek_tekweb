<?php
class Post {
    private $conn;
    private $table_name = "posts";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Create Post
    public function create($title, $content, $category_id, $user_id, $status, $image) {
        $query = "INSERT INTO {$this->table_name}
                  (title, content, category_id, user_id, status, image)
                  VALUES (:title, :content, :category_id, :user_id, :status, :image)";

        $stmt = $this->conn->prepare($query);

        // Sanitasi
        $title = htmlspecialchars(strip_tags($title));

        // NOTE:
        // Jika pakai editor HTML → hapus strip_tags untuk content
        $content = htmlspecialchars(strip_tags($content));

        // Binding
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":image", $image);

        return $stmt->execute();
    }

    // 2. Read All Posts
    public function getAll() {
        $query = "SELECT id, title, status, image, created_at
                  FROM {$this->table_name}
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 3. Upload Image
    public function uploadImage($file) {
        if (empty($file['name'])) return null;

        $target_dir = "../uploads/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allowed)) return false;

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $file_name;
        }

        return false;
    }

    // 4. Delete Post
    public function delete($id) {

        $queryGet = "SELECT image FROM {$this->table_name} WHERE id = :id";
        $stmtGet = $this->conn->prepare($queryGet);
        $stmtGet->bindParam(":id", $id);
        $stmtGet->execute();
        $row = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['image'] && file_exists("../uploads/" . $row['image'])) {
            unlink("../uploads/" . $row['image']);
        }

        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
?>
