<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getUser();

require_once __DIR__ . '/../db.php';

$msg = '';

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'] ?? '';
    
    // Handle category creation
    if ($action === 'create_category'){
        $cat_name = trim($_POST['cat_name'] ?? '');
        $cat_slug = trim($_POST['cat_slug'] ?? '');
        if ($cat_name === '') {
            $msg = 'Category name required.';
        } else {
            $stmt = $mysqli->prepare('INSERT INTO topher_categories (name, slug) VALUES (?, ?)');
            $stmt->bind_param('ss', $cat_name, $cat_slug);
            $msg = $stmt->execute() ? 'Category created successfully!' : 'Failed to create category.';
            $stmt->close();
        }
    }
    // Handle post creation/update
    else {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' 
                        ? (int)$_POST['category_id'] 
                        : null;
        $image = trim($_POST['image'] ?? '');

        if ($action === 'create'){
            // insert post
            $stmt = $mysqli->prepare(
                'INSERT INTO topher_posts (title, excerpt, content, category_id, image, published_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            $stmt->bind_param('sssis', $title, $excerpt, $content, $category_id, $image);
            $msg = $stmt->execute() ? 'Post created.' : 'Insert failed.';
            $stmt->close();

        } elseif ($action === 'update'){
            // update post
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $mysqli->prepare(
                'UPDATE topher_posts 
                 SET title=?, excerpt=?, content=?, category_id=?, image=? 
                 WHERE id=?'
            );
            $stmt->bind_param('sssisi', $title, $excerpt, $content, $category_id, $image, $id);
            $msg = $stmt->execute() ? 'Post updated.' : 'Update failed.';
            $stmt->close();
        }
    }
}

// DELETE
if (isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare('DELETE FROM topher_posts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: posts.php');
    exit;
}

// FETCH DATA
$cats = db_fetch_all('SELECT id, name FROM topher_categories ORDER BY name');
$posts = db_fetch_all(
    'SELECT p.id, p.title, p.excerpt, p.content, p.category_id, p.image, p.published_at,
            c.name AS category_name
     FROM topher_posts p
     LEFT JOIN topher_categories c ON p.category_id = c.id
     ORDER BY p.published_at DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Postingan - Mini CMS</title>
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
                <span class="text-gray-300">|</span> Kelola Postingan
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

    <div class="p-4 sm:p-8 max-w-7xl mx-auto">

<?php if ($msg): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
        <i class="fas fa-check-circle mr-2"></i><?=htmlspecialchars($msg)?>
    </div>
<?php endif; ?>

        <!-- LIST POSTS -->
        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500 mb-8">
            <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                <i class="fas fa-list"></i> Daftar Postingan
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Published</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $p): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <span class="text-gray-600 font-semibold"><?=htmlspecialchars($p['id'])?></span>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 font-semibold"><?=htmlspecialchars($p['title'])?></p>
                                <?php if ($p['image']): ?>
                                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-image"></i> Has image</p>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <?php if ($p['category_name']): ?>
                                    <span class="px-3 py-1 font-semibold text-blue-900 leading-tight bg-blue-100 rounded-full text-xs">
                                        <?=htmlspecialchars($p['category_name'])?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">No category</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-gray-600">
                                <?=htmlspecialchars($p['published_at'])?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <a href="#edit-<?=htmlspecialchars($p['id'])?>" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded transition mr-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?=htmlspecialchars($p['id'])?>" onclick="return confirm('Yakin hapus postingan ini?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CREATE POST & CATEGORY GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- CREATE CATEGORY -->
            <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-purple-500 h-fit">
                <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <i class="fas fa-folder-plus"></i> Buat Kategori Baru
                </h2>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="create_category">
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-tag text-gray-400"></i> Nama Kategori
                        </label>
                        <input name="cat_name" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="Nama kategori">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-link text-gray-400"></i> Slug
                        </label>
                        <input name="cat_slug" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="url-friendly-slug">
                        <p class="text-xs text-gray-500 mt-1">*Opsional, untuk URL</p>
                    </div>

                    <button type="submit" class="w-full bg-purple-600 text-white font-bold py-2 px-4 rounded hover:bg-purple-700 transition shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>

            <!-- CREATE POST -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border-t-4 border-green-500">
                <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Buat Postingan Baru
                </h2>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="create">

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-heading text-gray-400"></i> Title
                        </label>
                        <input name="title" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="Judul postingan">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-align-left text-gray-400"></i> Excerpt
                        </label>
                        <textarea name="excerpt" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="Ringkasan singkat"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-file-alt text-gray-400"></i> Content
                        </label>
                        <textarea name="content" required rows="6" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="Konten lengkap postingan"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-folder text-gray-400"></i> Category
                        </label>
                        <div class="relative">
                            <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded appearance-none focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                                <option value="">-- none --</option>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?=htmlspecialchars($c['id'])?>">
                                        <?=htmlspecialchars($c['name'])?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fas fa-image text-gray-400"></i> Image URL
                        </label>
                        <input id="createImageUrl" name="image" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="https://images.unsplash.com/photo-...">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i> Paste any image URL from the internet. Try: 
                            <a href="https://unsplash.com" target="_blank" class="text-blue-500 hover:underline">Unsplash</a>, 
                            <a href="https://pexels.com" target="_blank" class="text-blue-500 hover:underline">Pexels</a>, or 
                            <a href="https://imgur.com" target="_blank" class="text-blue-500 hover:underline">Imgur</a>
                        </p>
                        <div id="createImagePreview" class="mt-2 hidden">
                            <img src="" alt="Preview" class="max-w-full h-32 object-cover rounded border border-gray-300">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded hover:bg-green-700 transition shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i> Buat Postingan
                    </button>
                </form>
            </div>
        </div>

        <!-- EDIT FORMS -->
        <?php foreach ($posts as $p): ?>
        <div id="edit-<?=htmlspecialchars($p['id'])?>" class="bg-white p-6 rounded-lg shadow-md border-t-4 border-yellow-500 mb-8">
            <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Post #<?=htmlspecialchars($p['id'])?>: <?=htmlspecialchars($p['title'])?>
            </h2>

            <form method="post" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?=htmlspecialchars($p['id'])?>">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-heading text-gray-400"></i> Title
                    </label>
                    <input name="title" value="<?=htmlspecialchars($p['title'])?>" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-align-left text-gray-400"></i> Excerpt
                    </label>
                    <textarea name="excerpt" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition"><?=htmlspecialchars($p['excerpt'])?></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-file-alt text-gray-400"></i> Content
                    </label>
                    <textarea name="content" required rows="6" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition"><?=htmlspecialchars($p['content'])?></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-folder text-gray-400"></i> Category
                    </label>
                    <div class="relative">
                        <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded appearance-none focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white">
                            <option value="">-- none --</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?=htmlspecialchars($c['id'])?>"
                                    <?= $p['category_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?=htmlspecialchars($c['name'])?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-image text-gray-400"></i> Image URL
                    </label>
                    <input name="image" value="<?=htmlspecialchars($p['image'])?>" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition">
                </div>

                <button type="submit" class="w-full bg-yellow-500 text-white font-bold py-3 px-4 rounded hover:bg-yellow-600 transition shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i> Update Postingan
                </button>
            </form>
        </div>
        <?php endforeach; ?>

    </div>

<script>
// Image URL Preview for Create Post Form
document.getElementById('createImageUrl').addEventListener('input', function(e) {
    const url = e.target.value.trim();
    const preview = document.getElementById('createImagePreview');
    const img = preview.querySelector('img');
    
    if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
        img.src = url;
        preview.classList.remove('hidden');
        
        // Handle image load error
        img.onerror = function() {
            preview.classList.add('hidden');
        };
    } else {
        preview.classList.add('hidden');
    }
});

// Image URL Preview for Edit Post Forms (dynamic)
document.querySelectorAll('input[name="image"]').forEach(function(input) {
    if (input.id !== 'createImageUrl') { // Skip the create form, already handled
        const formId = input.closest('form').querySelector('input[name="id"]')?.value;
        if (formId) {
            // Create preview element if it doesn't exist
            let preview = input.parentElement.querySelector('.image-preview');
            if (!preview && input.value) {
                preview = document.createElement('div');
                preview.className = 'image-preview mt-2';
                preview.innerHTML = '<img src="' + input.value + '" alt="Preview" class="max-w-full h-32 object-cover rounded border border-gray-300">';
                input.parentElement.appendChild(preview);
            }
            
            // Add input listener
            input.addEventListener('input', function(e) {
                const url = e.target.value.trim();
                let preview = input.parentElement.querySelector('.image-preview');
                
                if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview mt-2';
                        preview.innerHTML = '<img src="" alt="Preview" class="max-w-full h-32 object-cover rounded border border-gray-300">';
                        input.parentElement.appendChild(preview);
                    }
                    const img = preview.querySelector('img');
                    img.src = url;
                    preview.style.display = 'block';
                    
                    img.onerror = function() {
                        preview.style.display = 'none';
                    };
                } else if (preview) {
                    preview.style.display = 'none';
                }
            });
        }
    }
});
</script>

</body>
</html>