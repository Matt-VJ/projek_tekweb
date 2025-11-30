<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

// DB
$db = Database::getInstance()->getConnection();

//HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: posts.php?msg=deleted");
    exit;
}

//HANDLE SAVE (ADD / UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $cat_id  = intval($_POST['category']);
    $image   = trim($_POST['image']);
    $post_id = $_POST['post_id'] ?? null;

    if ($post_id) {
        // UPDATE
        $sql = "UPDATE posts SET title=:t, content=:c, category_id=:cat, image=:img WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t' => $title,
            ':c' => $content,
            ':cat' => $cat_id,
            ':img' => $image,
            ':id' => $post_id
        ]);
    } else {
        // INSERT
        $sql = "INSERT INTO posts (title, content, category_id, image) 
                VALUES (:t, :c, :cat, :img)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t' => $title,
            ':c' => $content,
            ':cat' => $cat_id,
            ':img' => $image
        ]);
    }

    header("Location: posts.php?msg=saved");
    exit;
}

//FETCH POSTS & CATEGORY
$posts = $db->query("
    SELECT posts.*, categories.name AS cat_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    ORDER BY posts.id DESC
")->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

//EDIT MODE
$editPost = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $editPost = $stmt->fetch();
}

?>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 p-6">

    <h1 class="text-2xl font-bold mb-4">Kelola Postingan</h1>

    <?php if (isset($_GET['msg'])): ?>
        <div class="mb-4 p-3 bg-green-200 text-green-800 rounded">
            <?= $_GET['msg'] === 'saved' ? "Berhasil disimpan!" : "Berhasil dihapus!" ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="bg-white p-5 rounded shadow mb-8 w-full max-w-2xl">
        <h2 class="text-xl font-semibold mb-3">
            <?= $editPost ? "Edit Postingan" : "Tambah Postingan" ?>
        </h2>

        <form method="POST">

            <?php if ($editPost): ?>
                <input type="hidden" name="post_id" value="<?= $editPost['id'] ?>">
            <?php endif; ?>

            <!-- TITLE -->
            <label class="font-semibold">Judul</label>
            <input 
                type="text" 
                name="title" 
                required
                value="<?= $editPost ? htmlspecialchars($editPost['title']) : "" ?>"
                class="w-full p-2 border rounded mb-3 break-words break-all"
            >

            <!-- CATEGORY -->
            <label class="font-semibold">Kategori</label>
            <select name="category" class="w-full p-2 border rounded mb-3">
                <?php foreach ($categories as $c): ?>
                    <option 
                        value="<?= $c['id'] ?>"
                        <?= $editPost && $editPost['category_id'] == $c['id'] ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- IMAGE URL -->
            <label class="font-semibold">URL Gambar</label>
            <input 
                type="text" 
                name="image"
                value="<?= $editPost ? htmlspecialchars($editPost['image']) : "" ?>"
                class="w-full p-2 border rounded mb-3 break-words break-all"
            >

            <!-- CONTENT -->
            <label class="font-semibold">Konten</label>
            <textarea 
                name="content" 
                required
                class="w-full p-3 border rounded mb-3 break-words break-all min-h-[200px]"
            ><?= $editPost ? htmlspecialchars($editPost['content']) : "" ?></textarea>

            <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
            >
                Simpan
            </button>
        </form>
    </div>

    <!-- LIST -->
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Daftar Postingan</h2>

        <table class="w-full border-collapse">
            <tr class="bg-gray-100 text-left">
                <th class="p-2">Judul</th>
                <th class="p-2">Kategori</th>
                <th class="p-2">Aksi</th>
            </tr>

            <?php foreach ($posts as $p): ?>
                <tr class="border-t">
                    <td class="p-2 break-words break-all">
                        <?= htmlspecialchars($p['title']) ?>
                    </td>
                    <td class="p-2 text-gray-600">
                        <?= htmlspecialchars($p['cat_name'] ?? '-') ?>
                    </td>
                    <td class="p-2">
                        <a 
                            href="posts.php?edit=<?= $p['id'] ?>" 
                            class="text-blue-600 mr-3"
                        >Edit</a>

                        <a 
                            href="posts.php?delete=<?= $p['id'] ?>" 
                            class="text-red-600"
                            onclick="return confirm('Yakin ingin menghapus?')"
                        >Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>

</body>
</html>
