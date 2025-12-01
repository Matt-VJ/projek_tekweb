<?php
$db_config = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '',
    'name' => 'mini_cms',
    'port' => 3306,
];

$mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name'], $db_config['port']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Database connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}

$mysqli->set_charset('utf8mb4');

function db_fetch_all($sql){
    global $mysqli;
    $res = $mysqli->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

function db_escape($s){ global $mysqli; return $mysqli->real_escape_string($s); }

?>
