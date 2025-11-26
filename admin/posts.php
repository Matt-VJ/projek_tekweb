<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$msg = '';

// Create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $image = trim($_POST['image'] ?? '');

    if ($action === 'create'){
        $stmt = $mysqli->prepare('INSERT INTO topher_posts (title, excerpt, content, category_id, image, published_at) VALUES (?, ?, ?, ?, ?, NOW())');
        // types: title(s), excerpt(s), content(s), category_id(i), image(s)
        $stmt->bind_param('sssis', $title, $excerpt, $content, $category_id, $image);
        if ($stmt->execute()) $msg = 'Post created.'; else $msg = 'Insert failed.';
        $stmt->close();
    } elseif ($action === 'update'){
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $mysqli->prepare('UPDATE topher_posts SET title=?, excerpt=?, content=?, category_id=?, image=? WHERE id=?');
        $stmt->bind_param('sssisi', $title, $excerpt, $content, $category_id, $image, $id);
        if ($stmt->execute()) $msg = 'Post updated.'; else $msg = 'Update failed.';
        $stmt->close();
    }
}

// Delete
if (isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare('DELETE FROM topher_posts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: posts.php');
    exit;
}

$cats = db_fetch_all('SELECT id, name FROM topher_categories ORDER BY name');
$posts = db_fetch_all('SELECT p.id, p.title, p.excerpt, p.published_at, c.name AS category_name FROM topher_posts p LEFT JOIN topher_categories c ON p.category_id = c.id ORDER BY p.published_at DESC');

?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin - Posts</title></head>
<body>
<p><a href="categories.php">Categories</a> | <a href="logout.php">Logout</a></p>
<h1>Posts</h1>
<?php if ($msg): ?><p style="color:green"><?=htmlspecialchars($msg)?></p><?php endif; ?>

<table border="1" cellpadding="6">
    <tr><th>ID</th><th>Title</th><th>Category</th><th>Published</th><th>Action</th></tr>
    <?php foreach ($posts as $p): ?>
    <tr>
        <td><?=htmlspecialchars($p['id'])?></td>
        <td><?=htmlspecialchars($p['title'])?></td>
        <td><?=htmlspecialchars($p['category_name'])?></td>
        <td><?=htmlspecialchars($p['published_at'])?></td>
        <td>
            <a href="#edit-<?=htmlspecialchars($p['id'])?>">Edit</a> |
            <a href="?delete=<?=htmlspecialchars($p['id'])?>" onclick="return confirm('Delete?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Create Post</h2>
<form method="post">
    <input type="hidden" name="action" value="create">
    <label>Title: <input name="title"></label><br>
    <label>Excerpt: <textarea name="excerpt"></textarea></label><br>
    <label>Content: <textarea name="content"></textarea></label><br>
    <label>Category: <select name="category_id">
        <option value="">-- none --</option>
        <?php foreach ($cats as $c): ?>
            <option value="<?=htmlspecialchars($c['id'])?>"><?=htmlspecialchars($c['name'])?></option>
        <?php endforeach; ?>
    </select></label><br>
    <label>Image URL: <input name="image"></label><br>
    <button type="submit">Create</button>
</form>

</body>
</html>
