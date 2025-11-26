<?php
require_once __DIR__ . '/_auth.php';
if (isset($_SESSION['topher_admin']) && $_SESSION['topher_admin'] === true){
    header('Location: categories.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $pw = isset($_POST['password']) ? $_POST['password'] : '';
    if ($pw === TOPHER_ADMIN_PASSWORD){
        $_SESSION['topher_admin'] = true;
        header('Location: categories.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Login</title></head>
<body>
<h1>Topher Admin Login</h1>
<?php if ($error): ?><p style="color:red"><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post">
    <label>Password: <input type="password" name="password"></label>
    <button type="submit">Login</button>
</form>
</body>
</html>
