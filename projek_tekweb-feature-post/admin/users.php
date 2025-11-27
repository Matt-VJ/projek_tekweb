<?php
// FILE: admin/users.php
session_start();

// --- 1. Load Classes ---
require_once '../classes/Database.php';
require_once '../classes/Auth.php'; 
require_once '../classes/User.php';

// --- 2. Proteksi Halaman (Wajib Admin!) ---
$auth = new Auth();
$auth->requireAdmin(); // Cek login & role admin sekaligus
$user_session = $auth->getUser(); // Untuk menampilkan nama di navbar

$userObj = new User();
$message = "";

// --- 3. Handle Create User ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $result = $userObj->createUser($username, $password, $role);
    if ($result === true) {
        // Refresh halaman agar form bersih kembali
        header("Location: users.php?msg=success");
        exit;
    } else {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4'>$result</div>";
    }
}

// Cek pesan sukses dari redirect
if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4'>User berhasil ditambahkan!</div>";
}

// --- 4. Handle Delete User ---
if (isset($_GET['delete_id'])) {
    $idToDelete = $_GET['delete_id'];
    if ($idToDelete == $_SESSION['user_id']) {
        echo "<script>alert('Dilarang menghapus akun sendiri!'); window.location='users.php';</script>";
    } else {
        if ($userObj->deleteUser($idToDelete)) {
            header("Location: users.php");
        }
    }
}

// Ambil semua data user
$users = $userObj->getAllUsers();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <div class="font-bold text-xl text-blue-600 flex items-center gap-2">
                <span class="text-gray-300">|</span> Manajemen User
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-gray-800 font-semibold text-sm">Halo, <?= htmlspecialchars($user_session['username']) ?></p>
                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-0.5 rounded-full uppercase tracking-wide">
                    <?= ucfirst($user_session['role']) ?>
                </span>
            </div>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Logout</span>
            </a>
        </div>
    </nav>

    <div class="p-4 sm:p-8 max-w-6xl mx-auto">
        
        <?= $message ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-white p-6 rounded-lg shadow-md h-fit border-t-4 border-blue-500">
                <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Tambah User Baru
                </h2>
                <form method="POST">
                    <input type="hidden" name="create_user" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Username unik">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Password user">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Role (Peran)</label>
                        <div class="relative">
                            <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="editor">Editor</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">*Admin memiliki akses penuh.</p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-1"></i> Simpan User
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border-t-4 border-purple-600">
                <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <i class="fas fa-users"></i> Daftar Pengguna
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10">
                                            <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-gray-900 whitespace-no-wrap font-bold">
                                                <?= htmlspecialchars($u['username']) ?>
                                            </p>
                                            <p class="text-gray-400 text-xs">ID: <?= $u['id'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="px-3 py-1 font-semibold text-purple-900 leading-tight bg-purple-100 rounded-full text-xs">Admin</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 font-semibold text-blue-900 leading-tight bg-blue-100 rounded-full text-xs">Editor</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?delete_id=<?= $u['id'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="Hapus User">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Akun Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>