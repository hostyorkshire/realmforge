<?php
/**
 * Compress Memory API Endpoint
 * Compresses player history into a story summary.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../engine/memory.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['state'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing state']);
    exit;
}

$state = compressStoryMemory($body['state']);

echo json_encode(['state' => $state]);
