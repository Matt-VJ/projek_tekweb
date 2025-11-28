<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Comment.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getUser();

$db = Database::getInstance()->getConnection();
$commentObj = new Comment($db);

$msg = '';
$msgType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    
    if ($action === 'approve' && $comment_id > 0) {
        if ($commentObj->updateStatus($comment_id, 'approved')) {
            $msg = 'Comment approved successfully.';
            $msgType = 'success';
        } else {
            $msg = 'Failed to approve comment.';
            $msgType = 'error';
        }
    } elseif ($action === 'spam' && $comment_id > 0) {
        if ($commentObj->updateStatus($comment_id, 'spam')) {
            $msg = 'Comment marked as spam.';
            $msgType = 'success';
        } else {
            $msg = 'Failed to mark as spam.';
            $msgType = 'error';
        }
    } elseif ($action === 'pending' && $comment_id > 0) {
        if ($commentObj->updateStatus($comment_id, 'pending')) {
            $msg = 'Comment moved to pending.';
            $msgType = 'success';
        } else {
            $msg = 'Failed to move to pending.';
            $msgType = 'error';
        }
    } elseif ($action === 'delete' && $comment_id > 0) {
        if ($commentObj->delete($comment_id)) {
            $msg = 'Comment deleted successfully.';
            $msgType = 'success';
        } else {
            $msg = 'Failed to delete comment.';
            $msgType = 'error';
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$status = null;
if ($filter === 'pending') $status = 'pending';
elseif ($filter === 'approved') $status = 'approved';
elseif ($filter === 'spam') $status = 'spam';

// Fetch comments
$comments = $commentObj->getAll($status);

// Count by status
$stmt = $db->query("SELECT status, COUNT(*) as count FROM comments GROUP BY status");
$counts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $counts[$row['status']] = $row['count'];
}
$total_count = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Navigation -->
    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="Dashboard.php" class="text-gray-500 hover:text-blue-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <div class="font-bold text-xl text-blue-600 flex items-center gap-2">
                <span class="text-gray-300">|</span> Moderasi Komentar
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="../home.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-home"></i> <span class="hidden sm:inline">View Site</span>
            </a>
            <div class="text-right hidden sm:block">
                <p class="text-gray-800 font-semibold text-sm">Halo, <?= htmlspecialchars($user['username']) ?></p>
                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-0.5 rounded-full uppercase tracking-wide">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Logout</span>
            </a>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-7xl">
        
        <!-- Message -->
        <?php if ($msg): ?>
            <div class="mb-4 p-4 rounded <?= $msgType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex border-b">
                <a href="?filter=all" class="px-6 py-3 <?= $filter === 'all' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600' ?>">
                    All (<?= $total_count ?>)
                </a>
                <a href="?filter=approved" class="px-6 py-3 <?= $filter === 'approved' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600' ?>">
                    <i class="fas fa-check-circle text-green-500"></i> Approved (<?= $counts['approved'] ?? 0 ?>)
                </a>
                <a href="?filter=pending" class="px-6 py-3 <?= $filter === 'pending' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600' ?>">
                    <i class="fas fa-clock text-yellow-500"></i> Pending (<?= $counts['pending'] ?? 0 ?>)
                </a>
                <a href="?filter=spam" class="px-6 py-3 <?= $filter === 'spam' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600' ?>">
                    <i class="fas fa-ban text-red-500"></i> Spam (<?= $counts['spam'] ?? 0 ?>)
                </a>
            </div>
        </div>

        <!-- Comments List -->
        <div class="space-y-4">
            <?php if (count($comments) === 0): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-6xl mb-4 text-gray-300"></i>
                    <p class="text-xl">No comments found</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-bold text-lg text-gray-800">
                                        <i class="fas fa-user-circle text-gray-400"></i>
                                        <?= htmlspecialchars($comment['username'] ?? $comment['author_name']) ?>
                                    </span>
                                    <?php if ($comment['author_email']): ?>
                                        <span class="text-sm text-gray-500">
                                            (<?= htmlspecialchars($comment['author_email']) ?>)
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- Status Badge -->
                                    <?php if ($comment['status'] === 'approved'): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                            <i class="fas fa-check-circle"></i> Approved
                                        </span>
                                    <?php elseif ($comment['status'] === 'pending'): ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">
                                            <i class="fas fa-ban"></i> Spam
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-sm text-gray-500 mb-3">
                                    <i class="fas fa-calendar"></i> <?= date('F j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-newspaper"></i> Post: 
                                    <a href="../post_detail.php?id=<?= $comment['post_id'] ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?= htmlspecialchars($comment['post_title']) ?>
                                    </a>
                                </div>
                                
                                <div class="text-gray-700 bg-gray-50 p-4 rounded border-l-4 border-blue-500">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-4 border-t">
                            <?php if ($comment['status'] !== 'approved'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($comment['status'] !== 'pending'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="pending">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm">
                                        <i class="fas fa-clock"></i> Pending
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($comment['status'] !== 'spam'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="spam">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm">
                                        <i class="fas fa-ban"></i> Spam
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this comment?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
