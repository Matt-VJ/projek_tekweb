<div class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out h-min-screen">
    
    <a href="dashboard.php" class="text-white flex items-center space-x-2 px-4">
        <i class="fas fa-rocket text-2xl text-blue-500"></i>
        <span class="text-2xl font-extrabold">Mini CMS</span>
    </a>

    <nav>
        <a href="dashboard.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-home mr-2"></i> Dashboard
        </a>

        <a href="posts.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-file-alt mr-2"></i> Postingan
        </a>

        <a href="categories.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-tags mr-2"></i> Kategori
        </a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="border-t border-gray-700 mt-4 pt-4 px-4 text-xs text-gray-400 uppercase font-bold">Admin Area</div>
            <a href="users.php" class="block py-2.5 px-4 rounded transition duration-200 bg-gray-700 text-white mt-2">
                <i class="fas fa-users-cog mr-2"></i> Users
            </a>
        <?php endif; ?>
    </nav>

    <div class="absolute bottom-0 w-full px-4 pb-4">
        <a href="../logout.php" class="block py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white text-center transition">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>