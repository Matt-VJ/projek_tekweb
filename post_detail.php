<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Comment.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean(); 
    header('Content-Type: application/json');

    $post_id_in = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $author_name = trim($_POST['author_name'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($post_id_in <= 0 || empty($author_name) || empty($content)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan Komentar wajib diisi!']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();
        $commentObj = new Comment($db);

        if ($commentObj->create($post_id_in, $content, $author_name)) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'author' => htmlspecialchars($author_name),
                    'content' => nl2br(htmlspecialchars($content)),
                    'initial' => strtoupper(substr($author_name, 0, 1))
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan database.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) die('Invalid Post ID');

global $mysqli;
$stmt = $mysqli->prepare("SELECT p.*, c.name AS category_name FROM topher_posts p LEFT JOIN topher_categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) die('Post not found');

$db = Database::getInstance()->getConnection();
$commentObj = new Comment($db);
$comments = $commentObj->getByPostId($post_id);
$comment_count = count($comments);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .break-safe { word-break: break-word; overflow-wrap: break-word; }
        .break-all-force { word-break: break-all; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .new-comment { animation: slideIn 0.5s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="home.php" class="text-blue-600 font-medium hover:underline flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Home
            </a>
            <span class="font-bold text-gray-700">Mini CMS</span>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 py-8">

        <div class="mb-12 bg-white p-8 rounded-lg shadow-sm">
            <h1 class="text-3xl font-bold mb-4 leading-tight break-safe text-gray-900">
                <?= htmlspecialchars($post['title']) ?>
            </h1>
            
            <div class="text-sm text-gray-500 mb-6 pb-4 border-b border-gray-200">
                Category: <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?> | 
                Published: <?= date('F d, Y', strtotime($post['published_at'])) ?>
            </div>

            <?php if ($post['image']): ?>
                <img src="<?= htmlspecialchars($post['image']) ?>" class="w-full h-auto rounded-lg mb-6 shadow-sm">
            <?php endif; ?>

            <div class="text-lg leading-relaxed text-gray-800 break-safe">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-sm">
            
            <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800">
                <i class="far fa-comments"></i> Komentar ( <span id="count"><?= $comment_count ?></span> )
            </h3>

            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100 mb-8">
                <h4 class="font-bold text-gray-700 mb-4">Tinggalkan Komentar</h4>
                <div id="msgBox"></div>

                <form id="commentForm">
                    <input type="hidden" name="post_id" value="<?= $post_id ?>">
                    
                    <input type="text" name="author_name" required 
                           class="w-full mb-3 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 bg-white"
                           placeholder="Nama Anda">
                    
                    <textarea name="content" required rows="3"
                              class="w-full mb-4 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 bg-white"
                              placeholder="Tulis komentar..."></textarea>
                    
                    <button type="submit" id="btnSubmit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-sm transition">
                        Kirim Komentar
                    </button>
                </form>
            </div>

            <div id="commentsList" class="space-y-6">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="border-b border-gray-100 pb-4 last:border-0">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-gray-900 break-all-force mr-4">
                                    <?= htmlspecialchars($c['author_name']) ?>
                                </span>
                                <span class="text-xs text-gray-400 whitespace-nowrap mt-1">
                                    <?= date('d M Y, H:i', strtotime($c['created_at'])) ?>
                                </span>
                            </div>
                            <div class="text-gray-600 leading-relaxed break-safe">
                                <?= nl2br(htmlspecialchars($c['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p id="noCommentsMsg" class="text-center text-gray-400 italic py-4">Belum ada komentar. Jadilah yang pertama!</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script>
    document.getElementById('commentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btnSubmit');
        const msgBox = document.getElementById('msgBox');
        const originalText = btn.innerText;
        
        btn.disabled = true;
        btn.innerText = 'Mengirim...';
        msgBox.innerHTML = '';

        try {
            const res = await fetch(window.location.href, {
                method: 'POST',
                body: new FormData(this)
            });

            const text = await res.text();
            let json;
            try { json = JSON.parse(text); } 
            catch (e) { throw new Error("Server Error"); }

            if (json.status === 'success') {
                msgBox.innerHTML = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-sm border border-green-200">Komentar berhasil dikirim!</div>';
                
                const newHtml = `
                    <div class="border-b border-gray-100 pb-4 last:border-0 new-comment bg-blue-50 p-3 rounded -mx-3 mb-2">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-gray-900 break-all-force mr-4">${json.data.author}</span>
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-1">Baru saja</span>
                        </div>
                        <div class="text-gray-700 leading-relaxed break-safe">${json.data.content}</div>
                    </div>
                `;

                const noMsg = document.getElementById('noCommentsMsg');
                if(noMsg) noMsg.remove();

                document.getElementById('commentsList').insertAdjacentHTML('afterbegin', newHtml);
                const countSpan = document.getElementById('count');
                countSpan.innerText = parseInt(countSpan.innerText) + 1;

                this.reset();
                setTimeout(() => { msgBox.innerHTML = ''; }, 3000);

            } else {
                msgBox.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm border border-red-200">${json.message}</div>`;
            }

        } catch (err) {
            msgBox.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm border border-red-200">Terjadi kesalahan koneksi.</div>`;
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    });
    </script>

</body>
</html>