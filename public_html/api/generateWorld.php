<?php
/**
 * Generate World API Endpoint
 * Generates or returns the world map.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/continentGenerator.php';
require_once __DIR__ . '/../../engine/world.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$world = loadWorld();

if (!$world) {
    // Generate world on first launch
    $world = generateWorld();

    // Queue world map image generation (non-blocking — client can request separately)
}

echo json_encode([
    'world'   => $world,
    'towns'   => $world['towns']   ?? [],
    'dungeons'=> $world['dungeons'] ?? [],
    'kingdoms'=> $world['kingdoms'] ?? [],
]);
