<?php
require_once __DIR__ . '/db.php';
$categories = db_fetch_all("SELECT id, name FROM topher_categories ORDER BY name");

$initial_posts = db_fetch_all("SELECT p.id, p.title, p.excerpt, p.image, p.published_at, c.name AS category_name, c.id AS category_id,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
    FROM topher_posts p
    LEFT JOIN topher_categories c ON p.category_id = c.id
    ORDER BY p.published_at DESC LIMIT 6");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Home - Mini CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <i class="fas fa-rocket text-blue-600 text-2xl"></i>
                    <span class="font-bold text-xl text-blue-900 tracking-tight">Mini CMS</span>
                </div>
                <div>
                    <a href="Login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition shadow-md flex items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-xl p-8 md:p-12 mb-10 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Selamat Datang di Mini CMS</h1>
                <p class="text-blue-100 text-lg max-w-2xl">
                    Jelajahi postingan terbaru, cari berdasarkan kategori, dan baca komentar dari pembaca. Platform berbagi konten yang cepat dan mudah.
                </p>
            </div>
            <i class="fas fa-layer-group absolute right-0 bottom-0 text-9xl text-white opacity-10 transform translate-x-10 translate-y-10"></i>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                <div class="md:col-span-3">
                    <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Kategori:</label>
                    <div class="relative">
                        <select id="category" class="w-full appearance-none bg-gray-50 border border-gray-300 text-gray-700 py-2.5 px-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?=htmlspecialchars($cat['id'])?>"><?=htmlspecialchars($cat['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-7">
                    <label for="q" class="block text-sm font-semibold text-gray-700 mb-2">Cari Judul:</label>
                    <div class="relative">
                        <input id="q" type="search" placeholder="Ketik judul artikel..." class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2.5 px-4 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <button id="apply" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 px-4 rounded-lg transition shadow-sm h-[46px]">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>

        <section id="grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (count($initial_posts) === 0): ?>
                <div class="col-span-full text-center py-20">
                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                    <p class="text-xl text-gray-500 font-medium">Belum ada postingan.</p>
                </div>
            <?php else: ?>
                <?php foreach ($initial_posts as $p): ?>
                    <article class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 cursor-pointer flex flex-col h-full group" 
                             onclick="location.href='post_detail.php?id=<?=htmlspecialchars($p['id'])?>'">
                        
                        <div class="h-48 overflow-hidden bg-gray-200 relative">
                            <?php if ($p['image']): ?>
                                <img src="<?=htmlspecialchars($p['image'])?>" alt="<?=htmlspecialchars($p['title'])?>" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300">
                                    <i class="fas fa-image text-5xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm text-blue-700 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                            </span>
                        </div>

                        <div class="p-6 flex flex-col flex-grow">
                            <h2 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 transition">
                                <?=htmlspecialchars($p['title'])?>
                            </h2>
                            <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">
                                <?=htmlspecialchars($p['excerpt'])?>
                            </p>
                            
                            <div class="border-t border-gray-100 pt-4 flex items-center justify-between text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d M Y', strtotime($p['published_at'])) ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="far fa-comments"></i>
                                    <?= $p['comment_count'] ?> Komentar
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div class="text-center mt-12">
            <button id="loadMore" class="bg-white border border-gray-300 text-gray-600 hover:text-blue-600 hover:border-blue-600 px-8 py-3 rounded-full font-medium transition shadow-sm">
                Load More Posts <i class="fas fa-arrow-down ml-2"></i>
            </button>
        </div>

    </main>

    <footer class="bg-white border-t border-gray-200 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; <?= date('Y') ?> Mini CMS Project. All rights reserved.
        </div>
    </footer>

    <script>
    let currentPage = 1;
    const PAGE_LIMIT = 6;
    let loading = false;
    let lastCount = null;

    function escapeHtml(s) {
        if (!s) return "";
        return s.toString().replace(/[&<>"]/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;'
        }[c]));
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    async function fetchPosts({append = false} = {}) {
        if (loading) return;
        
        const loadMoreBtn = document.getElementById('loadMore');
        loading = true;
        
        if(append) {
            loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }

        const category = document.getElementById('category').value;
        const q = document.getElementById('q').value.trim();
        const params = new URLSearchParams();
        
        if (category) params.set('category_id', category);
        if (q) params.set('q', q);
        params.set('page', currentPage);
        params.set('limit', PAGE_LIMIT);
        params.set('include_count', 1);

        try {
            const res = await fetch('fetch_posts.php?' + params.toString());
            const json = await res.json();
            const data = json.data || [];
            lastCount = json.count !== null ? json.count : lastCount;

            const grid = document.getElementById('grid');
            
            if (!append) grid.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                if (!append) {
                    grid.innerHTML = `
                        <div class="col-span-full text-center py-20">
                            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                            <p class="text-xl text-gray-500 font-medium">Tidak ada hasil ditemukan.</p>
                        </div>
                    `;
                }
                loadMoreBtn.style.display = 'none';
                loading = false;
                return;
            }

            for (const p of data) {
                const article = document.createElement('article');
                article.className = 'bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 cursor-pointer flex flex-col h-full group fade-in';
                article.onclick = () => location.href = `post_detail.php?id=${p.id}`;
                
                let imageHtml = '';
                if (p.image) {
                    imageHtml = `<img src="${escapeHtml(p.image)}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500" onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center bg-gray-100 text-gray-300\\'><i class=\\'fas fa-image text-5xl\\'></i></div>'">`;
                } else {
                    imageHtml = `<div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300"><i class="fas fa-image text-5xl"></i></div>`;
                }

                article.innerHTML = `
                    <div class="h-48 overflow-hidden bg-gray-200 relative">
                        ${imageHtml}
                        <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm text-blue-700 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                            ${escapeHtml(p.category_name || 'Uncategorized')}
                        </span>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <h2 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 transition">
                            ${escapeHtml(p.title)}
                        </h2>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">
                            ${escapeHtml(p.excerpt || '')}
                        </p>
                        <div class="border-t border-gray-100 pt-4 flex items-center justify-between text-xs text-gray-400">
                            <span class="flex items-center gap-1"><i class="far fa-calendar-alt"></i> ${formatDate(p.published_at)}</span>
                            <span class="flex items-center gap-1"><i class="far fa-comments"></i> ${p.comment_count || 0} Komentar</span>
                        </div>
                    </div>
                `;
                grid.appendChild(article);
            }

            if (lastCount !== null && grid.children.length >= lastCount) {
                loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.style.display = 'inline-block';
                loadMoreBtn.innerHTML = 'Load More Posts <i class="fas fa-arrow-down ml-2"></i>';
            }

        } catch (error) {
            console.error("Error fetching posts:", error);
        }

        loading = false;
    }

    document.getElementById('apply').addEventListener('click', e => { 
        currentPage = 1; 
        fetchPosts({append: false}); 
    });
    
    document.getElementById('q').addEventListener('keypress', e => { 
        if (e.key === 'Enter') { 
            currentPage = 1; 
            fetchPosts({append: false}); 
        } 
    });
    
    document.getElementById('loadMore').addEventListener('click', e => { 
        currentPage++; 
        fetchPosts({append: true}); 
    });

    </script>
</body>
</html>