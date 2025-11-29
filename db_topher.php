<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'mini-cms';
$db_user = 'root';  // Default XAMPP username
$db_pass = '';      // Default XAMPP password

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to fetch all categories
function get_categories($pdo) {
    $stmt = $pdo->query("SELECT * FROM topher_categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch posts with optional category filter
function get_posts($pdo, $category_id = null, $search = '') {
    $sql = "SELECT p.*, c.name as category_name 
            FROM topher_posts p 
            LEFT JOIN topher_categories c ON p.category_id = c.id 
            WHERE 1=1";
    
    $params = [];
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $sql .= " ORDER BY p.published_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
