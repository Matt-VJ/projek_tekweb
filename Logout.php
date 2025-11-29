<?php
// FILE: Logout.php
// Letakkan file ini di folder utama (sejajar dengan index.php dan Login.php)

// 1. Panggil class yang dibutuhkan agar sistem tahu apa itu "Auth"
require_once 'classes/Database.php';
require_once 'classes/Auth.php';

// 2. Buat objek Auth baru
$auth = new Auth();

// 3. Panggil fungsi logout
// Fungsi ini otomatis menghapus sesi dan melempar Anda kembali ke Login.php
$auth->logout();
?>