<?php
// FILE: register.php
// Letakkan di folder utama (sejajar dengan login.php)

require_once 'classes/Database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$msg = '';
$msgType = ''; // 'success' atau 'error'

// Proses saat tombol Daftar ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 1. Validasi Input Dasar
    if ($password !== $confirm) {
        $msg = "Password konfirmasi tidak cocok.";
        $msgType = 'error';
    } 
    elseif (strlen($password) < 6) {
        $msg = "Password minimal 6 karakter.";
        $msgType = 'error';
    }
    else {
        // 2. Panggil fungsi register dari Class Auth
        // Fungsi ini otomatis mendaftarkan user sebagai 'editor'
        $result = $auth->register($username, $password);
        
        if ($result === true) {
            // Jika sukses, arahkan ke login dengan pesan sukses
            header("Location: login.php?registered=true");
            exit;
        } else {
            // Jika gagal (misal username sudah ada), tampilkan pesan error
            $msg = $result;
            $msgType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Editor - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Buat Akun Baru</h2>
            <p class="text-gray-500 text-sm mt-1">Daftar sebagai Kontributor (Editor)</p>
        </div>
        
        <!-- Notifikasi Error -->
        <?php if($msg): ?>
            <div class="mb-4 p-3 rounded-lg text-sm border-l-4 <?php echo ($msgType == 'success') ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="" class="space-y-4">
            
            <!-- Username -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" class="w-full pl-10 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Pilih username unik" required 
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" class="w-full pl-10 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Minimal 6 karakter" required minlength="6">
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Ulangi Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <input type="password" name="confirm_password" class="w-full pl-10 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Masukkan password sekali lagi" required>
                </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md mt-6">
                Daftar Sekarang
            </button>

        </form>
        
        <!-- Link ke Login -->
        <div class="text-center mt-6 pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600">
                Sudah punya akun? <a href="login.php" class="text-blue-600 font-bold hover:underline">Login di sini</a>
            </p>
        </div>

    </div>
</body>
</html>