<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/Post.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getUser();

$postObj = new Post();
$db = Database::getInstance()->getConnection(); 


if (isset($_GET['delete_id'])) {
    if ($postObj->delete($_GET['delete_id'])) {
        header("Location: posts.php?msg=deleted");
        exit;
    }
}

$msg = "";
$msgType = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $title = $_POST['title'];
    $excerpt = $_POST['excerpt'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $image = $_POST['image'];

    if ($action === 'create') {
        if ($postObj->create($title, $excerpt, $content, $category_id, $image, $user['user_id'])) {
            $msg = "Berhasil dibuat!"; $msgType = "success";
        } else { $msg = "Gagal membuat."; $msgType = "error"; }
    } elseif ($action === 'update') {
        $id = $_POST['post_id'];
        if ($postObj->update($id, $title, $excerpt, $content, $category_id, $image)) {
            $msg = "Berhasil diupdate!"; $msgType = "success";
        } else { $msg = "Gagal update."; $msgType = "error"; }
    }
}

$posts = $postObj->getAll($user['role'], $user['user_id']);
$stmtCat = $db->query("SELECT * FROM topher_categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Kelola Postingan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>.modal { transition: opacity 0.25s ease; } body.modal-active { overflow: hidden; }</style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-white shadow-md px-4 md:px-6 py-4 flex flex-col md:flex-row justify-between items-center sticky top-0 z-40 gap-4 md:gap-0">
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 transition"><i class="fas fa-arrow-left"></i> <span class="hidden md:inline">Kembali</span></a>
                <div class="font-bold text-lg md:text-xl text-blue-600 flex items-center gap-2">
                    <span class="text-gray-300">|</span> Kelola Post
                </div>
            </div>
            <a href="../logout.php" class="md:hidden bg-red-500 text-white px-3 py-1 rounded text-xs"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        
        <div class="hidden md:flex items-center gap-4">
            <span class="text-sm font-semibold text-gray-600">Halo, <?= htmlspecialchars($user['username']) ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition">Logout</a>
        </div>
    </nav>

    <div class="p-4 md:p-6 max-w-7xl mx-auto">
        <?php if ($msg): ?>
            <div class="<?= $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> p-4 mb-4 rounded shadow-sm">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="flex justify-end mb-4">
            <button onclick="openModal('create')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 md:px-6 md:py-2 rounded-lg shadow transition flex items-center gap-2 text-sm md:text-base">
                <i class="fas fa-plus"></i> <span class="hidden md:inline">Tambah Postingan</span><span class="md:hidden">Baru</span>
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto"> <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap w-20">Cover</th>
                            <th class="px-5 py-3 border-b-2 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase min-w-[200px]">Judul</th>
                            <th class="px-5 py-3 border-b-2 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Kategori</th>
                            <th class="px-5 py-3 border-b-2 bg-gray-50 text-center text-xs font-semibold text-gray-600 uppercase whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $p): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-5 py-4 bg-white text-sm">
                                <?php if ($p['image']): ?>
                                    <img src="<?=htmlspecialchars($p['image'])?>" class="w-12 h-12 object-cover rounded border min-w-[3rem]">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-100 rounded border flex items-center justify-center min-w-[3rem]"><i class="fas fa-image text-gray-400"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 bg-white">
                                <div class="max-w-[150px] md:max-w-xs truncate font-bold text-gray-900" title="<?=htmlspecialchars($p['title'])?>">
                                    <?=htmlspecialchars($p['title'])?>
                                </div>
                                <div class="max-w-[150px] md:max-w-xs truncate text-xs text-gray-500">
                                    <?=htmlspecialchars($p['excerpt'])?>
                                </div>
                            </td>
                            <td class="px-5 py-4 bg-white text-sm whitespace-nowrap">
                                <span class="px-2 py-1 font-semibold text-blue-800 bg-blue-100 rounded-full text-xs">
                                    <?= htmlspecialchars($p['category_name'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 bg-white text-sm text-center whitespace-nowrap">
                                <div class="flex justify-center gap-2">
                                    <button onclick='openModal("edit", <?= json_encode($p) ?>)' class="text-yellow-500 hover:text-yellow-700 bg-yellow-50 p-2 rounded"><i class="fas fa-edit"></i></button>
                                    <a href="?delete_id=<?= $p['id'] ?>" onclick="return confirm('Yakin hapus?')" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="postModal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 p-4">
        <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
        <div class="modal-container bg-white w-full md:max-w-3xl mx-auto rounded-lg shadow-lg z-50 overflow-y-auto max-h-[90vh]">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3 border-b mb-4">
                    <p class="text-xl font-bold text-gray-800" id="modalTitle">Postingan</p>
                    <div class="modal-close cursor-pointer z-50 p-2" onclick="closeModal()"><i class="fas fa-times"></i></div>
                </div>
                <form method="POST" id="postForm" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="post_id" id="postId" value="">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Judul</label>
                        <input type="text" name="title" id="inputTitle" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                            <select name="category_id" id="inputCategory" class="w-full border rounded px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Pilih --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Gambar URL</label>
                            <input type="text" name="image" id="inputImage" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Ringkasan</label>
                        <textarea name="excerpt" id="inputExcerpt" rows="2" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Konten</label>
                        <textarea name="content" id="inputContent" rows="6" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>

                    <div class="flex justify-end pt-2 gap-2 border-t mt-4">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded text-sm font-bold text-gray-700">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-bold hover:bg-blue-700 shadow" id="submitBtn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.querySelector('.modal');
        const body = document.querySelector('body');
        function openModal(mode, data = null) {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            body.classList.add('modal-active');
            document.getElementById('postForm').reset();
            if (mode === 'edit' && data) {
                document.getElementById('modalTitle').innerText = 'Edit Postingan';
                document.getElementById('formAction').value = 'update';
                document.getElementById('postId').value = data.id;
                document.getElementById('inputTitle').value = data.title;
                document.getElementById('inputCategory').value = data.category_id || "";
                document.getElementById('inputImage').value = data.image;
                document.getElementById('inputExcerpt').value = data.excerpt;
                document.getElementById('inputContent').value = data.content;
                document.getElementById('submitBtn').innerText = 'Update';
            } else {
                document.getElementById('modalTitle').innerText = 'Buat Baru';
                document.getElementById('formAction').value = 'create';
                document.getElementById('postId').value = '';
                document.getElementById('submitBtn').innerText = 'Simpan';
            }
        }
        function closeModal() { modal.classList.add('opacity-0', 'pointer-events-none'); body.classList.remove('modal-active'); }
    </script>
</body>
</html>