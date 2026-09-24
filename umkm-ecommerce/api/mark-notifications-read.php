<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$db = getDB();
$db->exec('UPDATE notifications SET is_read = 1 WHERE is_read = 0');

echo json_encode(['success' => true]);
