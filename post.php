<?php
require_once __DIR__ . '/db.php';

// ambil id post
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// jika id tidak ada
if ($id <= 0){
    echo "Invalid post ID.";
    exit;
}

// query post + kategori
$stmt = $mysqli->prepare(
    "SELECT p.id, p.title, p.excerpt, p.content, p.image, p.published_at,
            c.name AS category_name
     FROM topher_posts p
     LEFT JOIN topher_categories c ON p.category_id = c.id
     WHERE p.id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

// cek post ada atau tidak
if (!$post){
    echo "Post not found.";
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
</head>
<body>

<p><a href="index.php">← Back to Homepage</a></p>

<h1><?= htmlspecialchars($post['title']) ?></h1>

<p><i>Category: <?= htmlspecialchars($post['category_name']) ?></i></p>
<p><i>Published: <?= htmlspecialchars($post['published_at']) ?></i></p>

<?php if (!empty($post['image'])): ?>
    <p><img src="<?= htmlspecialchars($post['image']) ?>" width="400"></p>
<?php endif; ?>

<p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

<hr>

<h2>Comments (coming soon)</h2>
<p>Feature by Anggota E</p>

</body>
</html>