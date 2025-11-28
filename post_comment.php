<?php
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Comment.php';

session_start();

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$author_name = isset($_POST['author_name']) ? trim($_POST['author_name']) : null;

// Validation
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Comment content is required']);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Comment is too long (max 1000 characters)']);
    exit;
}

// Require name only
if (empty($author_name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

// Sanitize input
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
$author_name = htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8');

// Create comment (always as guest, no user_id, no email)
try {
    $db = Database::getInstance()->getConnection();
    $commentObj = new Comment($db);
    
    $result = $commentObj->create($post_id, $content, $author_name, null, null);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Comment posted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to post comment']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
