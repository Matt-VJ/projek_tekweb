<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Comment.php';

// Start session to check if user is logged in
session_start();

// Get post ID
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    die('Invalid post ID');
}

// Fetch post details
$post_query = "SELECT p.*, c.name AS category_name 
               FROM topher_posts p
               LEFT JOIN topher_categories c ON p.category_id = c.id
               WHERE p.id = ?";
$stmt = $mysqli->prepare($post_query);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die('Post not found');
}

// Get comments
$db = Database::getInstance()->getConnection();
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
    <link rel="stylesheet" href="assets/css/topher.css">
    <style>
        .post-detail {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .post-header {
            margin-bottom: 30px;
        }
        .post-meta {
            color: #666;
            font-size: 14px;
            margin: 10px 0;
        }
        .post-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin: 20px 0;
        }
        .post-content {
            line-height: 1.8;
            font-size: 16px;
            margin: 30px 0;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3b82f6;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* Comments Section */
        .comments-section {
            margin-top: 50px;
            border-top: 2px solid #e5e7eb;
            padding-top: 30px;
        }
        .comments-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .comment-form {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .comment-form input,
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: inherit;
        }
        .comment-form textarea {
            min-height: 100px;
            resize: vertical;
        }
        .comment-form button {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        .comment-form button:hover {
            background: #2563eb;
        }
        .comment-form button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .comment-list {
            margin-top: 20px;
        }
        .comment {
            background: white;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .comment-author {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .comment-date {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .comment-content {
            color: #374151;
            line-height: 1.6;
        }
        .no-comments {
            text-align: center;
            color: #6b7280;
            padding: 30px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="post-detail">
    <a href="home.php" class="back-link">← Back to Home</a>
    
    <article class="post-header">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div class="post-meta">
            <span>Category: <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></span>
            <span> | </span>
            <span>Published: <?= date('F j, Y', strtotime($post['published_at'])) ?></span>
        </div>
        
        <?php if ($post['image']): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-image">
        <?php endif; ?>
        
        <div class="post-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
    </article>

    <!-- Comments Section -->
    <section class="comments-section">
        <h2 class="comments-header">Comments (<?= $comment_count ?>)</h2>
        
        <!-- Comment Form -->
        <div class="comment-form">
            <h3>Leave a Comment</h3>
            <div id="message"></div>
            <form id="commentForm">
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                
                <input type="text" name="author_name" placeholder="Your Name *" required>
                
                <textarea name="content" placeholder="Write your comment here... *" required></textarea>
                <button type="submit" id="submitBtn">Post Comment</button>
            </form>
        </div>

        <!-- Comments List -->
        <div class="comment-list" id="commentList">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-author">
                            <?= htmlspecialchars($comment['username'] ?? $comment['author_name']) ?>
                        </div>
                        <div class="comment-date">
                            <?= date('F j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comments">No comments yet. Be the first to comment!</div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
document.getElementById('commentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('message');
    
    // Disable button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';
    messageDiv.innerHTML = '';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('post_comment.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.innerHTML = '<div class="success-message">Comment posted successfully!</div>';
            
            // Clear form
            document.querySelector('[name="author_name"]').value = '';
            document.querySelector('[name="content"]').value = '';
            
            // Reload comments
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            messageDiv.innerHTML = '<div class="error-message">' + result.message + '</div>';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="error-message">An error occurred. Please try again.</div>';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Post Comment';
    }
});
</script>
</body>
</html>
