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
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin - Posts</title></head>
<body>

<p>
    <a href="../home.php" target="_blank" style="color: #3b82f6; font-weight: bold;">🏠 View Site</a> | 
    <a href="Dashboard.php">Dashboard</a> | 
    <a href="categories.php">Categories</a> | 
    <a href="comments.php">Comments</a> | 
    <a href="../logout.php">Logout</a>
</p>

<h1>Posts</h1>

<?php if ($msg): ?>
    <p style="color:green"><?=htmlspecialchars($msg)?></p>
<?php endif; ?>

<!-- LIST POSTS -->
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Category</th>
        <th>Published</th>
        <th>Action</th>
    </tr>

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

<!-- CREATE POST -->
<h2>Create Post</h2>
<form method="post">
    <input type="hidden" name="action" value="create">

    <label>Title: <input name="title" required></label><br>

    <label>Excerpt:
        <textarea name="excerpt" required></textarea>
    </label><br>

    <label>Content:
        <textarea name="content" required></textarea>
    </label><br>

    <label>Category:
        <select name="category_id">
            <option value="">-- none --</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?=htmlspecialchars($c['id'])?>">
                    <?=htmlspecialchars($c['name'])?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Image URL:
        <input name="image">
    </label><br>

    <button type="submit">Create</button>
</form>

<hr>

<!-- EDIT FORMS -->
<?php foreach ($posts as $p): ?>
<h2 id="edit-<?=htmlspecialchars($p['id'])?>">Edit Post #<?=htmlspecialchars($p['id'])?></h2>

<form method="post">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?=htmlspecialchars($p['id'])?>">

    <label>Title:
        <input name="title" value="<?=htmlspecialchars($p['title'])?>" required>
    </label><br>

    <label>Excerpt:
        <textarea name="excerpt" required><?=htmlspecialchars($p['excerpt'])?></textarea>
    </label><br>

    <label>Content:
        <textarea name="content" required><?=htmlspecialchars($p['content'])?></textarea>
    </label><br>

    <label>Category:
        <select name="category_id">
            <option value="">-- none --</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?=htmlspecialchars($c['id'])?>"
                    <?= $p['category_id'] == $c['id'] ? 'selected' : '' ?>>
                    <?=htmlspecialchars($c['name'])?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Image URL:
        <input name="image" value="<?=htmlspecialchars($p['image'])?>">
    </label><br>

    <button type="submit">Update</button>
</form>

<hr>
<?php endforeach; ?>

</body>
</html>