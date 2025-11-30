<?php
require_once __DIR__ . '/db.php';

$categories = db_fetch_all("SELECT id, name FROM topher_categories ORDER BY name");

$initial_posts = db_fetch_all("SELECT p.id, p.title, p.excerpt, p.image, p.published_at, c.name AS category_name,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
    FROM topher_posts p
    LEFT JOIN topher_categories c ON p.category_id = c.id
    ORDER BY p.published_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

    <!-- Header -->
    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="font-bold text-xl text-blue-600 flex items-center gap-2">
            <i class="fas fa-rocket"></i> Mini CMS
        </div>
        <div class="flex items-center gap-4">
            <a href="Login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-sign-in-alt"></i> <span class="hidden sm:inline">Login</span>
            </a>
        </div>
    </nav>

    <!-- Welcome Banner -->
    <div class="p-4 sm:p-8 max-w-5xl mx-auto">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-xl shadow-lg mb-8 relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang di Mini CMS</h1>
                <p class="opacity-90">Jelajahi postingan terbaru, cari berdasarkan kategori, dan baca komentar dari pembaca.</p>
            </div>
            <i class="fas fa-layer-group absolute -right-4 -bottom-4 text-9xl text-white opacity-10"></i>
        </div>

        <!-- Filters -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 flex flex-wrap gap-4 items-center">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori:</label>
                <select id="category" class="border border-gray-300 rounded px-3 py-2">
                    <option value="">Semua</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1">
                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari Judul:</label>
                <input id="q" type="search" placeholder="Cari judul..." class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <button id="apply" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-medium mt-6 sm:mt-0">
                Terapkan
            </button>
        </div>

        <!-- Post Grid -->
        <section id="grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (count($initial_posts) === 0): ?>
                <p class="text-gray-500">Belum ada postingan.</p>
            <?php else: ?>
                <?php foreach ($initial_posts as $p): ?>
                    <article class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden cursor-pointer" onclick="location.href='post_detail.php?id=<?= $p['id'] ?>'">
                        <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <?php if ($p['image']): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="object-cover w-full h-full">
                            <?php else: ?>
                                <i class="fas fa-image text-4xl text-white opacity-50"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h2 class="font-bold text-lg text-gray-800 mb-1"><?= htmlspecialchars($p['title']) ?></h2>
                            <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($p['excerpt']) ?></p>
                            <p class="text-xs text-gray-500">Kategori: <?= htmlspecialchars($p['category_name']) ?></p>
                            <p class="text-xs text-gray-500 mt-1"><i class="fas fa-comments"></i> <?= $p['comment_count'] ?> komentar</p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- Load More -->
        <div class="text-center mt-8">
            <button id="loadMore" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded font-medium">
                Load More
            </button>
        </div>
    </div>

<script>
let currentPage = 1;
const PAGE_LIMIT = 6;
let loading = false;
let lastCount = null;

async function fetchPosts({append=false} = {}){
    if (loading) return;
    loading = true;
    const category = document.getElementById('category').value;
    const q = document.getElementById('q').value.trim();
    const params = new URLSearchParams();
    if (category) params.set('category_id', category);
    if (q) params.set('q', q);
    params.set('page', currentPage);
    params.set('limit', PAGE_LIMIT);
    params.set('include_count', 1);

    const res = await fetch('fetch_posts.php?' + params.toString());
    const json = await res.json();
    const data = json.data || [];
    lastCount = json.count !== null ? json.count : lastCount;

    const grid = document.getElementById('grid');
    if (!append) grid.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0){
        if (!append) grid.innerHTML = '<p class="text-gray-500">Tidak ditemukan postingan.</p>';
        loading = false;
        return;
    }
    for (const p of data){
        const article = document.createElement('article');
        article.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden cursor-pointer';
        article.onclick = () => location.href = `post_detail.php?id=${p.id}`;
        article.innerHTML = `
            <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                ${p.image ? `<img src="${p.image}" class="object-cover w-full h-full">` : '<i class="fas fa-image text-4xl text-white opacity-50"></i>'}
            </div>
            <div class="p-4">
                <h2 class="font-bold text-lg text-gray-800 mb-1">${escapeHtml(p.title)}</h2>
                <p class="text-sm text-gray-600 mb-2">${escapeHtml(p.excerpt || '')}</p>
                <p class="text-xs text-gray-500">Kategori: ${escapeHtml(p.category_name || '')}</p>
                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-comments"></i> ${p.comment_count || 0} komentar</p>
            </div>
        `;
        grid.appendChild(article);
    }

    const loadMore = document.getElementById('loadMore');
    if (lastCount !== null && grid.children.length >= lastCount) loadMore.style.display = 'none';
    else loadMore.style.display = '';

    loading = false;
}

function escapeHtml(s){ return s.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

document.getElementById('apply').addEventListener('click', e => { currentPage = 1; fetchPosts({append:false}); });
document.getElementById('q').addEventListener('keypress', e => { if (e.key === 'Enter') { currentPage = 1; fetchPosts({append:false}); } });
document.getElementById('loadMore').addEventListener('click', e => { currentPage++; fetchPosts({append:true}); });
</script>

</body>
</html>
