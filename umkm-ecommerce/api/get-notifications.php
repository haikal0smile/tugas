<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$db = getDB();
$unread = (int)$db->query('SELECT COUNT(*) c FROM notifications WHERE is_read = 0')->fetch()['c'];

$stmt = $db->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20');
$items = $stmt->fetchAll();
foreach ($items as &$it) {
    $it['created_at'] = date('d M, H:i', strtotime($it['created_at']));
}

echo json_encode(['unread' => $unread, 'items' => $items]);
