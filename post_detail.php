<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Comment.php';

session_start();

$db = Database::getInstance()->getConnection(); // ✅ This is a PDO connection

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) die('Invalid post ID');

$post_query = "SELECT p.*, c.name AS category_name 
               FROM topher_posts p
               LEFT JOIN topher_categories c ON p.category_id = c.id
               WHERE p.id = ?";

$stmt = $db->prepare($post_query);
$stmt->execute([$post_id]);   // ✅ PDO style
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) die('Post not found');

$commentObj = new Comment($db);
$comments = $commentObj->getByPostId($post_id);
$comment_count = $commentObj->getCountByPostId($post_id);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($post['title']) ?> - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">
<div class="max-w-3xl mx-auto p-6">

    <a href="home_public.php" class="text-blue-600 hover:underline mb-4 inline-block">← Back to Home</a>
    
    <article class="bg-white rounded-lg shadow p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 break-words"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="text-sm text-gray-500 mt-2 mb-4">
            <span>Category: <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></span> |
            <span>Published: <?= $post['published_at'] ? date('F j, Y', strtotime($post['published_at'])) : '—' ?></span>
        </div>
        
        <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full max-h-96 object-cover rounded mb-6">
        <?php endif; ?>
        
        <div class="prose max-w-none break-words">
            <?= nl2br(htmlspecialchars($post['content'] ?? '')) ?>
        </div>
    </article>

    <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Comments (<?= (int)$comment_count ?>)</h2>
        
        <div id="commentList" class="space-y-4">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="border border-gray-200 rounded p-4">
                        <div class="font-bold text-gray-800 mb-1">
                            <?= htmlspecialchars($comment['username'] ?? $comment['author_name'] ?? 'Anonymous') ?>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">
                            <?= !empty($comment['created_at']) ? date('F j, Y \a\t g:i A', strtotime($comment['created_at'])) : '' ?>
                        </div>
                        <div class="text-gray-700 break-words">
                            <?= nl2br(htmlspecialchars($comment['content'] ?? '')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-500 bg-gray-100 rounded p-6">
                    No comments yet. Be the first to comment!
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
</body>
</html>
