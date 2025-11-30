<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getUser();

$db = Database::getInstance()->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: posts.php?msg=deleted");
    exit;
}

// Handle save (add / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $cat_id  = intval($_POST['category']);
    $image   = trim($_POST['image']);
    $post_id = $_POST['post_id'] ?? null;

    if ($post_id) {
        $sql = "UPDATE posts SET title=:t, content=:c, category_id=:cat, image=:img WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t' => $title,
            ':c' => $content,
            ':cat' => $cat_id,
            ':img' => $image,
            ':id' => $post_id
        ]);
        $msg = 'saved';
    } else {
        $sql = "INSERT INTO posts (title, content, category_id, image) VALUES (:t, :c, :cat, :img)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t' => $title,
            ':c' => $content,
            ':cat' => $cat_id,
            ':img' => $image
        ]);
        $msg = 'saved';
    }

    header("Location: posts.php?msg={$msg}");
    exit;
}

// Fetch posts & categories
$posts = $db->query("
    SELECT posts.*, categories.name AS cat_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    ORDER BY posts.id DESC
")->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Edit mode
$editPost = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $editPost = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Postingan - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

<!-- Top Navigation -->
<nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <div class="font-bold text-xl text-blue-600 flex items-center gap-2">
        <i class="fas fa-rocket"></i> Mini CMS
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

<!-- Main Content -->
<div class="p-4 sm:p-8 max-w-5xl mx-auto">

    <!-- Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-xl shadow-lg mb-8 relative overflow-hidden">
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2">Kelola Postingan</h1>
            <p class="opacity-90">Tambah, edit, atau hapus artikel yang tampil di halaman publik.</p>
        </div>
        <i class="fas fa-newspaper absolute -right-4 -bottom-4 text-9xl text-white opacity-10"></i>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="mb-6 p-4 rounded shadow
            <?= $_GET['msg'] === 'saved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= $_GET['msg'] === 'saved' ? "✅ Postingan berhasil disimpan!" : "🗑️ Postingan berhasil dihapus!" ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-10">
        <h2 class="text-xl font-bold mb-4"><?= $editPost ? "Edit Postingan" : "Tambah Postingan" ?></h2>
        <form method="POST" class="space-y-4">
            <?php if ($editPost): ?>
                <input type="hidden" name="post_id" value="<?= $editPost['id'] ?>">
            <?php endif; ?>

            <div>
                <label class="block font-semibold mb-1">Judul</label>
                <input type="text" name="title" required
                       value="<?= $editPost ? htmlspecialchars($editPost['title']) : "" ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Kategori</label>
                <select name="category" class="w-full border border-gray-300 rounded px-3 py-2">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= $editPost && $editPost['category_id'] == $c['id'] ? "selected" : "" ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">URL Gambar</label>
                <input type="text" name="image"
                       value="<?= $editPost ? htmlspecialchars($editPost['image']) : "" ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Konten</label>
                <textarea name="content" required
                          class="w-full border border-gray-300 rounded px-3 py-2 min-h-[200px]"><?= $editPost ? htmlspecialchars($editPost['content']) : "" ?></textarea>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium">
                Simpan
            </button>
        </form>
    </div>

    <!-- Post List -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Daftar Postingan</h2>
            <a href="posts.php" class="text-sm text-blue-600 hover:underline">Reset form</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="p-3">Judul</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $p): ?>
                        <tr class="border-t">
                            <td class="p-3 align-top"><?= htmlspecialchars($p['title']) ?></td>
                            <td class="p-3 align-top text-gray-600"><?= htmlspecialchars($p['cat_name'] ?? '-') ?></td>
                            <td class="p-3 align-top">
                                <a href="posts.php?edit=<?= $p['id'] ?>" class="text-blue-600 hover:underline mr-4">Edit</a>
                                <a href="posts.php?delete=<?= $p['id'] ?>"
                                   class="text-red-600 hover:underline"
                                   onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="border-t">
                        <td colspan="3" class="p-3 text-gray-500">Belum ada postingan.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
