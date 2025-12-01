<?php
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/Comment.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getUser();

$db = Database::getInstance()->getConnection();
$commentObj = new Comment($db);

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    
    if ($comment_id > 0) {
        if ($action === 'approve') {
            if ($commentObj->updateStatus($comment_id, 'approved')) $msg = 'Komentar disetujui.'; $msgType = 'success';
        } elseif ($action === 'spam') {
            if ($commentObj->updateStatus($comment_id, 'spam')) $msg = 'Komentar ditandai SPAM.'; $msgType = 'success';
        } elseif ($action === 'pending') {
            if ($commentObj->updateStatus($comment_id, 'pending')) $msg = 'Komentar dikembalikan ke Pending.'; $msgType = 'success';
        } elseif ($action === 'delete') {
            if ($commentObj->delete($comment_id)) $msg = 'Komentar dihapus permanen.'; $msgType = 'success';
        }
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$status = null;
if ($filter === 'pending') $status = 'pending';
elseif ($filter === 'approved') $status = 'approved';
elseif ($filter === 'spam') $status = 'spam';

$comments = $commentObj->getAll($status, $user['role'], $user['user_id']);

$sqlCount = "SELECT c.status, COUNT(*) as count 
             FROM comments c 
             LEFT JOIN topher_posts p ON c.post_id = p.id 
             WHERE 1=1";

if ($user['role'] !== 'admin') {
    $sqlCount .= " AND p.user_id = :uid";
}
$sqlCount .= " GROUP BY c.status";

$stmt = $db->prepare($sqlCount);
if ($user['role'] !== 'admin') {
    $stmt->execute([':uid' => $user['user_id']]);
} else {
    $stmt->execute();
}

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
    <title>Moderasi Komentar - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-white shadow-md px-4 md:px-6 py-4 flex flex-col md:flex-row justify-between items-center sticky top-0 z-50 gap-4 md:gap-0">
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> 
                    <span class="hidden md:inline">Kembali</span>
                </a>
                <div class="font-bold text-lg md:text-xl text-blue-600 flex items-center gap-2">
                    <span class="text-gray-300">|</span> Moderasi
                </div>
            </div>
            
            <a href="../logout.php" class="md:hidden bg-red-500 text-white px-3 py-1 rounded text-xs shadow-sm">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        
        <div class="hidden md:flex items-center gap-4">
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

    <div class="container mx-auto p-4 md:p-6 max-w-7xl">
        
        <?php if ($msg): ?>
            <div class="mb-4 p-4 rounded shadow-sm flex items-center gap-2 <?= $msgType === 'success' ? 'bg-green-100 text-green-700 border-l-4 border-green-500' : 'bg-red-100 text-red-700 border-l-4 border-red-500' ?>">
                <i class="fas <?= $msgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
            <div class="flex border-b overflow-x-auto">
                <a href="?filter=all" class="px-6 py-4 whitespace-nowrap <?= $filter === 'all' ? 'border-b-2 border-blue-600 text-blue-600 font-bold bg-blue-50' : 'text-gray-600 hover:bg-gray-50' ?>">
                    All (<?= $total_count ?>)
                </a>
                <a href="?filter=approved" class="px-6 py-4 whitespace-nowrap <?= $filter === 'approved' ? 'border-b-2 border-blue-600 text-blue-600 font-bold bg-blue-50' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i> Approved (<?= $counts['approved'] ?? 0 ?>)
                </a>
                <a href="?filter=pending" class="px-6 py-4 whitespace-nowrap <?= $filter === 'pending' ? 'border-b-2 border-blue-600 text-blue-600 font-bold bg-blue-50' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-clock text-yellow-500 mr-1"></i> Pending (<?= $counts['pending'] ?? 0 ?>)
                </a>
                <a href="?filter=spam" class="px-6 py-4 whitespace-nowrap <?= $filter === 'spam' ? 'border-b-2 border-blue-600 text-blue-600 font-bold bg-blue-50' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-ban text-red-500 mr-1"></i> Spam (<?= $counts['spam'] ?? 0 ?>)
                </a>
            </div>
        </div>

        <div class="space-y-4">
            <?php if (count($comments) === 0): ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center text-gray-400">
                    <i class="fas fa-inbox text-6xl mb-4 text-gray-200"></i>
                    <p class="text-xl">Tidak ada komentar di kategori ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition hover:shadow-md">
                        
                        <div class="flex flex-col md:flex-row justify-between items-start mb-3 gap-2">
                            <div class="flex-1 min-w-0 w-full">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="font-bold text-lg text-gray-800 truncate max-w-[200px] md:max-w-xs block" title="<?= htmlspecialchars($comment['username'] ?? $comment['author_name']) ?>">
                                        <i class="fas fa-user-circle text-gray-400 mr-1"></i>
                                        <?= htmlspecialchars($comment['username'] ?? $comment['author_name']) ?>
                                    </span>
                                    
                                    <?php 
                                        $statusClass = match($comment['status']) {
                                            'approved' => 'bg-green-100 text-green-700 border-green-200',
                                            'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            'spam' => 'bg-red-100 text-red-700 border-red-200',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                        $icon = match($comment['status']) {
                                            'approved' => 'fa-check',
                                            'pending' => 'fa-clock',
                                            'spam' => 'fa-ban',
                                            default => 'fa-question'
                                        };
                                    ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full border flex-shrink-0 <?= $statusClass ?>">
                                        <i class="fas <?= $icon ?>"></i> <?= ucfirst($comment['status']) ?>
                                    </span>
                                </div>

                                <div class="text-sm text-gray-500 flex items-center gap-2 flex-wrap">
                                    <span class="whitespace-nowrap"><i class="fas fa-calendar-alt"></i> <?= date('d M Y, H:i', strtotime($comment['created_at'])) ?></span>
                                    <span class="hidden sm:inline">|</span>
                                    <span class="flex items-center gap-1 min-w-0 max-w-full">
                                        <i class="fas fa-newspaper"></i> Post: 
                                        <a href="../post_detail.php?id=<?= $comment['post_id'] ?>" target="_blank" class="text-blue-600 hover:underline truncate max-w-[150px] md:max-w-sm inline-block align-bottom" title="<?= htmlspecialchars($comment['post_title']) ?>">
                                            <?= htmlspecialchars($comment['post_title']) ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-gray-700 bg-gray-50 p-4 rounded border-l-4 border-blue-400 break-all whitespace-normal">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>

                        <div class="grid grid-cols-2 md:flex gap-2 mt-4 pt-4 border-t border-gray-100">
                            <?php if ($comment['status'] !== 'approved'): ?>
                                <form method="POST" class="w-full md:w-auto"><input type="hidden" name="action" value="approve"><input type="hidden" name="comment_id" value="<?= $comment['id'] ?>"><button class="w-full md:w-auto px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-sm"><i class="fas fa-check"></i> Approve</button></form>
                            <?php endif; ?>
                            
                            <?php if ($comment['status'] !== 'pending'): ?>
                                <form method="POST" class="w-full md:w-auto"><input type="hidden" name="action" value="pending"><input type="hidden" name="comment_id" value="<?= $comment['id'] ?>"><button class="w-full md:w-auto px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm"><i class="fas fa-clock"></i> Pending</button></form>
                            <?php endif; ?>
                            
                            <?php if ($comment['status'] !== 'spam'): ?>
                                <form method="POST" class="w-full md:w-auto"><input type="hidden" name="action" value="spam"><input type="hidden" name="comment_id" value="<?= $comment['id'] ?>"><button class="w-full md:w-auto px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm"><i class="fas fa-ban"></i> Spam</button></form>
                            <?php endif; ?>
                            
                            <form method="POST" class="w-full md:w-auto" onsubmit="return confirm('Hapus permanen?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <button class="w-full md:w-auto px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
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