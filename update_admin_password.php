<?php
require_once __DIR__ . '/classes/Database.php';

$db = Database::getInstance()->getConnection(); // PDO connection

$newPassword = password_hash('admin123', PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $db->prepare($sql);
$stmt->execute([$newPassword]);

echo "✅ Admin password updated to 'admin123'";
?>
