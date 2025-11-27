<?php
// FILE: login.php (Versi Desain Keren - Tanpa Hitungan Percobaan)
require_once 'classes/Database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Cek pesan sukses dari register
if (isset($_GET['registered'])) {
    $success = "Registrasi berhasil! Silakan login.";
}

// Handler Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border border-gray-100 relative">
        
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Login CMS</h2>
            <p class="text-gray-400 text-sm mt-1">Masuk untuk mengelola konten</p>
        </div>
        
        <?php if($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 text-sm rounded">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- NOTIFIKASI ERROR KEREN (Tanpa Percobaan ke-X) -->
        <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded shadow-sm flex items-start">
                <div class="mt-0.5 mr-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                </div>
                <div>
                    <p class="font-bold text-base">Gagal Masuk</p>
                    <p class="text-red-600 mt-1"><?= $error ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="w-full pl-10 px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-gray-50 focus:bg-white" 
                           required placeholder="Masukkan username">
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="w-full pl-10 px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-gray-50 focus:bg-white" 
                           required placeholder="Masukkan password">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                Masuk Sekarang
            </button>
        </form>
        
        <div class="text-center mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Belum punya akun? <a href="register.php" class="text-blue-600 font-bold hover:underline">Daftar Editor</a>
            </p>
        </div>
    </div>
</body>
</html>