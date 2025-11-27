<?php
require_once __DIR__ . '/db.php';

$category_id = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$q = isset($_GET['q']) ? trim($_GET['q']) : null;

// pagination
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && (int)$_GET['limit'] > 0 ? (int)$_GET['limit'] : 6;
$offset = ($page - 1) * $limit;

$clauses = [];
if ($category_id) $clauses[] = "p.category_id = " . $category_id;
if ($q !== null && $q !== '') {
    $safe = db_escape('%' . $q . '%');
    $clauses[] = "p.title LIKE '" . $safe . "'";
}

$where = '';
if (count($clauses) > 0) $where = 'WHERE ' . implode(' AND ', $clauses);

$sql = "SELECT p.id, p.title, p.excerpt, p.image, p.published_at, c.name AS category_name, c.id AS category_id,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
    FROM topher_posts p
    LEFT JOIN topher_categories c ON p.category_id = c.id
    $where
    ORDER BY p.published_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$rows = db_fetch_all($sql);

// get total count for client if requested
$count = null;
if (isset($_GET['include_count']) && $_GET['include_count']){
    $count_sql = "SELECT COUNT(*) AS cnt FROM topher_posts p LEFT JOIN topher_categories c ON p.category_id = c.id $where";
    $crows = db_fetch_all($count_sql);
    $count = isset($crows[0]['cnt']) ? (int)$crows[0]['cnt'] : 0;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['page'=>$page, 'limit'=>$limit, 'count'=>$count, 'data'=>$rows]);
