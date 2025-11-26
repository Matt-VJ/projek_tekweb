<?php
require_once __DIR__ . '/_auth.php';
require_admin();
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
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin - Categories</title></head>
<body>
<p><a href="posts.php">Posts</a> | <a href="logout.php">Logout</a></p>
<h1>Categories</h1>
<?php if ($msg): ?><p style="color:green"><?=htmlspecialchars($msg)?></p><?php endif; ?>
<table border="1" cellpadding="6">
    <tr><th>ID</th><th>Name</th><th>Slug</th><th>Created</th><th>Action</th></tr>
    <?php foreach ($cats as $c): ?>
    <tr>
        <td><?=htmlspecialchars($c['id'])?></td>
        <td><?=htmlspecialchars($c['name'])?></td>
        <td><?=htmlspecialchars($c['slug'])?></td>
        <td><?=htmlspecialchars($c['created_at'])?></td>
        <td><a href="?delete=<?=htmlspecialchars($c['id'])?>" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Create Category</h2>
<form method="post">
    <input type="hidden" name="action" value="create">
    <label>Name: <input name="name"></label><br>
    <label>Slug: <input name="slug"></label><br>
    <button type="submit">Create</button>
 </form>

</body>
</html>
