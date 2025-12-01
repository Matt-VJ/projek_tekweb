<?php
session_start();

require_once '../classes/Database.php';
require_once '../classes/Auth.php'; 
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireAdmin();
$user_session = $auth->getUser();

$userObj = new User();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $result = $userObj->createUser($username, $password, $role);
    if ($result === true) {
        header("Location: users.php?msg=success");
        exit;
    } else {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded shadow-sm flex items-center gap-2'><i class='fas fa-exclamation-circle'></i> $result</div>";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded shadow-sm flex items-center gap-2'><i class='fas fa-check-circle'></i> User berhasil ditambahkan!</div>";
}

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

$users = $userObj->getAllUsers();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Manajemen User - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-white shadow-md px-4 md:px-6 py-4 flex flex-col md:flex-row justify-between items-center sticky top-0 z-50 gap-4 md:gap-0">
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> 
                    <span class="hidden md:inline">Kembali</span>
                </a>
                <div class="font-bold text-lg md:text-xl text-blue-600 flex items-center gap-2">
                    <span class="text-gray-300">|</span> Manajemen User
                </div>
            </div>
            
            <a href="../logout.php" class="md:hidden bg-red-500 text-white px-3 py-1 rounded text-xs shadow-sm">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        
        <div class="hidden md:flex items-center gap-4">
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

    <div class="p-4 md:p-6 max-w-6xl mx-auto">
        
        <?= $message ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            
            <div class="md:col-span-1 order-1 md:order-1">
                <div class="bg-white p-6 rounded-lg shadow-md h-fit border-t-4 border-blue-500 sticky top-24">
                    <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> Tambah User Baru
                    </h2>
                    <form method="POST">
                        <input type="hidden" name="create_user" value="1">
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                            <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username unik">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                            <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Password user">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Role (Peran)</label>
                            <div class="relative">
                                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="editor">Editor</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">*Admin memiliki akses penuh.</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition shadow-md flex justify-center items-center gap-2">
                            <i class="fas fa-save"></i> Simpan User
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2 order-2 md:order-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden border-t-4 border-purple-600">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-users"></i> Daftar Pengguna
                        </h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">User</th>
                                    <th class="px-5 py-3 border-b-2 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Role</th>
                                    <th class="px-5 py-3 border-b-2 bg-white text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50 transition border-b border-gray-100 last:border-0">
                                    <td class="px-5 py-4 bg-white text-sm">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10">
                                                <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200 shadow-sm">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 min-w-0">
                                                <p class="text-gray-900 font-bold truncate max-w-[120px] md:max-w-xs" title="<?= htmlspecialchars($u['username']) ?>">
                                                    <?= htmlspecialchars($u['username']) ?>
                                                </p>
                                                <p class="text-gray-400 text-xs">ID: <?= $u['id'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm whitespace-nowrap">
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="px-2 py-1 font-bold text-purple-700 bg-purple-100 rounded-full text-xs border border-purple-200">Admin</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 font-bold text-blue-700 bg-blue-100 rounded-full text-xs border border-blue-200">Editor</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm text-center whitespace-nowrap">
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="users.php?delete_id=<?= $u['id'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded block mx-auto w-8 h-8 flex items-center justify-center transition" title="Hapus User">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic">Saya</span>
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
    </div>

</body>
</html>