<?php
/**
 * Generate Dungeon API Endpoint
 * Returns a procedurally generated dungeon layout.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/dungeons.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$dungeonId = $_GET['id'] ?? '';

if (!$dungeonId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing dungeon id']);
    exit;
}

$dungeon = getDungeon($dungeonId);
if (!$dungeon) {
    http_response_code(404);
    echo json_encode(['error' => 'Dungeon not found']);
    exit;
}

$rooms = generateDungeonRooms($dungeonId);

echo json_encode([
    'dungeon' => $dungeon,
    'rooms'   => $rooms,
]);
