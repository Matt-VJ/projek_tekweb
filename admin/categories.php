<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/Category.php';

$auth = new Auth();
$auth->requireAdmin(); 
$user = $auth->getUser();
$catObj = new Category();
$msg = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        if ($catObj->create($name)) {
            $msg = "Kategori berhasil ditambahkan!";
            $msgType = "success";
        } else {
            $msg = "Gagal menambah kategori.";
            $msgType = "error";
        }
    } else {
        $msg = "Nama kategori tidak boleh kosong.";
        $msgType = "error";
    }
}

if (isset($_GET['delete_id'])) {
    if ($catObj->delete($_GET['delete_id'])) {
        header("Location: categories.php");
        exit;
    }
}

$categories = $catObj->getAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Kelola Kategori - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-white shadow-md px-4 md:px-6 py-4 flex flex-col md:flex-row justify-between items-center sticky top-0 z-50 gap-4 md:gap-0">
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 transition"><i class="fas fa-arrow-left"></i> <span class="hidden md:inline">Kembali</span></a>
                <div class="font-bold text-lg md:text-xl text-blue-600 flex items-center gap-2">
                    <span class="text-gray-300">|</span> Kelola Kategori
                </div>
            </div>
            <a href="../logout.php" class="md:hidden bg-red-500 text-white px-3 py-1 rounded text-xs"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        
        <div class="hidden md:flex items-center gap-4">
            <span class="text-sm font-semibold text-gray-600">Halo, <?= htmlspecialchars($user['username']) ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition">Logout</a>
        </div>
    </nav>

    <div class="p-4 md:p-6 max-w-6xl mx-auto">
        
        <?php if ($msg): ?>
            <div class="<?= $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> p-4 mb-6 rounded shadow-sm flex items-center gap-2">
                <i class="fas <?= $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            
            <div class="md:col-span-1 order-1 md:order-1">
                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500 sticky top-24">
                    <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Tambah Baru
                    </h2>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kategori</label>
                            <input type="text" name="name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Contoh: Teknologi">
                            <p class="text-xs text-gray-400 mt-1">Slug URL akan dibuat otomatis.</p>
                        </div>
                        <button type="submit" name="add_category" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition shadow">
                            Simpan Kategori
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2 order-2 md:order-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800">Daftar Kategori</h2>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?= count($categories) ?> Item</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Nama</th>
                                    <th class="px-5 py-3 border-b-2 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Slug</th>
                                    <th class="px-5 py-3 border-b-2 bg-white text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $c): ?>
                                <tr class="hover:bg-gray-50 transition border-b border-gray-100 last:border-0">
                                    <td class="px-5 py-3 bg-white text-sm font-bold text-gray-800 whitespace-nowrap">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </td>
                                    <td class="px-5 py-3 bg-white text-sm text-gray-500 font-mono text-xs whitespace-nowrap">
                                        <?= htmlspecialchars($c['slug']) ?>
                                    </td>
                                    <td class="px-5 py-3 bg-white text-sm text-center whitespace-nowrap">
                                        <a href="?delete_id=<?= $c['id'] ?>" onclick="return confirm('Yakin hapus?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded block mx-auto w-8 h-8 flex items-center justify-center">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if(empty($categories)): ?>
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-gray-400 italic">Belum ada kategori.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>