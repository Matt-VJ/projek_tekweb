<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getUser();

require_once __DIR__ . '/../db.php';

$msg = '';
// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create'){
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if ($name === '') $msg = 'Name required.';
    else {
        $stmt = $mysqli->prepare('INSERT INTO topher_categories (name, slug) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $slug);
        if ($stmt->execute()) $msg = 'Category created.'; else $msg = 'Insert failed.';
        $stmt->close();
    }
}

// Delete
if (isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare('DELETE FROM topher_categories WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: categories.php');
    exit;
}

$cats = db_fetch_all('SELECT id, name, slug, created_at FROM topher_categories ORDER BY name');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="Dashboard.php" class="text-gray-500 hover:text-blue-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <div class="font-bold text-xl text-blue-600 flex items-center gap-2">
                <span class="text-gray-300">|</span> Kelola Kategori
            </div>
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

    <div class="p-4 sm:p-8 max-w-6xl mx-auto">

<?php if ($msg): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
        <i class="fas fa-check-circle mr-2"></i><?=htmlspecialchars($msg)?>
    </div>
<?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- CREATE CATEGORY FORM -->
            <div class="bg-white p-6 rounded-lg shadow-md h-fit border-t-4 border-green-500">
                <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tambah Kategori Baru
                </h2>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-tag text-gray-400"></i> Name
                        </label>
                        <input name="name" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="Nama kategori">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-link text-gray-400"></i> Slug
                        </label>
                        <input name="slug" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="url-friendly-slug">
                        <p class="text-xs text-gray-500 mt-1">*Opsional, untuk URL</p>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700 transition shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>

            <!-- CATEGORY LIST -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500">
                <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list"></i> Daftar Kategori
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Slug</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cats as $c): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <span class="text-gray-600 font-semibold"><?=htmlspecialchars($c['id'])?></span>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 font-semibold"><?=htmlspecialchars($c['name'])?></p>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <span class="text-gray-600 font-mono text-xs"><?=htmlspecialchars($c['slug'])?></span>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-gray-600">
                                    <?=htmlspecialchars($c['created_at'])?>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                    <a href="?delete=<?=htmlspecialchars($c['id'])?>" onclick="return confirm('Yakin hapus kategori ini?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
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
