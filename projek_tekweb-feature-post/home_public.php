<?php
require_once __DIR__ . '/db.php';

// Server-side fetch for initial render (and graceful fallback)
$categories = db_fetch_all("SELECT id, name FROM categories ORDER BY name");

$initial_posts = db_fetch_all("SELECT p.id, p.title, p.excerpt, p.image, p.published_at, c.name AS category_name, c.id AS category_id
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.published_at DESC LIMIT 6");

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Public Homepage - Grid</title>
    <link rel="stylesheet" href="assets/css/public_grid.css">
</head>
<body>
<main class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Public Homepage</h1>
        <a href="Login.php" style="background: #3b82f6; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 500;">Login</a>
    </div>

    <div class="filters">
        <label for="category">Category:</label>
        <select id="category">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?=htmlspecialchars($cat['id'])?>"><?=htmlspecialchars($cat['name'])?></option>
            <?php endforeach; ?>
        </select>

        <label for="q">Search:</label>
        <input id="q" type="search" placeholder="Search titles...">
        <button id="apply">Apply</button>
    </div>

    <section id="grid" class="grid">
        <?php if (count($initial_posts) === 0): ?>
            <p>No posts yet.</p>
        <?php else: ?>
            <?php foreach ($initial_posts as $p): ?>
                <article class="card" data-id="<?=htmlspecialchars($p['id'])?>">
                    <div class="thumb"><?php if ($p['image']): ?><img src="<?=htmlspecialchars($p['image'])?>" alt="">
                    <?php else: ?><div class="placeholder"></div><?php endif; ?></div>
                    <div class="meta">
                        <h2><?=htmlspecialchars($p['title'])?></h2>
                        <p class="excerpt"><?=htmlspecialchars($p['excerpt'])?></p>
                        <p class="category"><?=htmlspecialchars($p['category_name'])?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</main>

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

    const res = await fetch('fetch_posts_public.php?' + params.toString());
    const json = await res.json();
    const data = json.data || [];
    lastCount = json.count !== null ? json.count : lastCount;

    const grid = document.getElementById('grid');
    if (!append) grid.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0){
        if (!append) grid.innerHTML = '<p>No posts found.</p>';
        loading = false;
        return;
    }
    for (const p of data){
        const article = document.createElement('article');
        article.className = 'card';
        article.dataset.id = p.id;
        article.innerHTML = `\n            <div class="thumb">${p.image ? `<img src="${p.image}">` : '<div class="placeholder"></div>'}</div>\n            <div class="meta">\n                <h2>${escapeHtml(p.title)}</h2>\n                <p class="excerpt">${escapeHtml(p.excerpt || '')}</p>\n                <p class="category">${escapeHtml(p.category_name || '')}</p>\n            </div>\n        `;
        grid.appendChild(article);
    }

    // update loadMore visibility
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
