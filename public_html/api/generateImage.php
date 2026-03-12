<?php
/**
 * Image Generation API Endpoint – Canvas stub
 *
 * Scene images are now generated entirely in the browser using HTML5 Canvas.
 * This endpoint is retained for backwards compatibility; it returns a JSON
 * object instructing the client to use its built-in canvas renderer instead
 * of fetching a server-generated image URL.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// No server-side image generation; signal the client to draw via Canvas.
echo json_encode(['canvas' => true]);
