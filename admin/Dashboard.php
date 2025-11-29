<?php
// FILE: admin/dashboard.php
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin(); // Tendang tamu yang belum login

$user = $auth->getUser(); // Ambil data session user (id, username, role)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

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

    <div class="p-4 sm:p-8 max-w-5xl mx-auto">
        
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-xl shadow-lg mb-8 relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang di Panel Kontrol</h1>
                <p class="opacity-90">Anda masuk sebagai <span class="font-bold bg-white/20 px-2 py-1 rounded"><?= strtoupper($user['role']) ?></span>. Silakan pilih menu di bawah ini untuk mengelola konten.</p>
            </div>
            <i class="fas fa-layer-group absolute -right-4 -bottom-4 text-9xl text-white opacity-10"></i>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-t-4 border-indigo-500 flex flex-col justify-between">
                <div>
                    <div class="text-indigo-500 text-3xl mb-4"><i class="fas fa-globe"></i></div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Lihat Website</h3>
                    <p class="text-gray-500 text-sm mb-4">Buka halaman publik untuk melihat postingan yang sudah dipublikasikan.</p>
                </div>
                <a href="../home.php" target="_blank" class="block bg-indigo-50 text-indigo-600 px-4 py-2 rounded hover:bg-indigo-600 hover:text-white transition text-center font-medium">
                    Buka Website &rarr;
                </a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-t-4 border-blue-500 flex flex-col justify-between">
                <div>
                    <div class="text-blue-500 text-3xl mb-4"><i class="fas fa-newspaper"></i></div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Kelola Postingan</h3>
                    <p class="text-gray-500 text-sm mb-4">Buat artikel baru, edit draft, atau publikasikan berita ke halaman utama.</p>
                </div>
                <a href="posts.php" class="block bg-blue-50 text-blue-600 px-4 py-2 rounded hover:bg-blue-600 hover:text-white transition text-center font-medium">
                    Buka Menu Post &rarr;
                </a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-t-4 border-green-500 flex flex-col justify-between">
                <div>
                    <div class="text-green-500 text-3xl mb-4"><i class="fas fa-comments"></i></div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Moderasi Komentar</h3>
                    <p class="text-gray-500 text-sm mb-4">Kelola komentar pembaca, approve, tandai spam, atau hapus komentar.</p>
                </div>
                <a href="comments.php" class="block bg-green-50 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white transition text-center font-medium">
                    Kelola Komentar &rarr;
                </a>
            </div>

            <?php if ($user['role'] === 'admin'): ?>
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-t-4 border-purple-600 flex flex-col justify-between">
                <div>
                    <div class="text-purple-600 text-3xl mb-4"><i class="fas fa-users-cog"></i></div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Manajemen User</h3>
                    <p class="text-gray-500 text-sm mb-4">Tambah Editor baru, reset password, atau hapus pengguna sistem.</p>
                </div>
                <a href="users.php" class="block bg-purple-50 text-purple-600 px-4 py-2 rounded hover:bg-purple-600 hover:text-white transition text-center font-medium">
                    Kelola User &rarr;
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>