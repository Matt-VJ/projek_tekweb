<?php
// Perhatikan path '../' karena file ini ada di dalam folder admin
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin(); // Tendang tamu yang belum login

$user = $auth->getUser();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
        <div class="font-bold text-xl text-blue-600">Mini CMS Dashboard</div>
        <div class="flex items-center gap-4">
            <span class="text-gray-600">
                Halo, <b><?= htmlspecialchars($user['username']) ?></b> 
                (<?= ucfirst($user['role']) ?>)
            </span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Content -->
    <div class="p-8 max-w-6xl mx-auto">
        
        <!-- Pesan Selamat Datang -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Selamat Datang di Panel Kontrol</h1>
            <p class="text-gray-600 mt-2">Anda login sebagai <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-bold"><?= strtoupper($user['role']) ?></span></p>
        </div>

        <!-- Menu Berdasarkan Role -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Menu Umum (Admin & Editor Bisa Akses) -->
            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-blue-500">
                <h3 class="font-bold text-lg mb-2"><i class="fas fa-newspaper"></i> Kelola Postingan</h3>
                <p class="text-gray-500 text-sm mb-4">Tulis, edit, dan publikasikan artikel blog.</p>
                <button class="bg-blue-50 text-blue-600 px-4 py-2 rounded w-full hover:bg-blue-100 text-left">
                    Buka Menu Post &rarr;
                </button>
            </div>

            <!-- Menu Khusus Admin (Hanya muncul jika Role = Admin) -->
            <?php if ($user['role'] === 'admin'): ?>
            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-purple-600">
                <h3 class="font-bold text-lg mb-2"><i class="fas fa-users-cog"></i> Manajemen User</h3>
                <p class="text-gray-500 text-sm mb-4">Angkat Editor menjadi Admin atau hapus user.</p>
                <button class="bg-purple-50 text-purple-600 px-4 py-2 rounded w-full hover:bg-purple-100 text-left">
                    Buka Manajemen User &rarr;
                </button>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>