<?php
/**
 * Image Generation API Endpoint
 * Generates or returns cached images using Stable Diffusion.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/imagePrompts.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$type    = $body['type']    ?? 'scene';
$context = $body['context'] ?? '';
$subtype = $body['subtype'] ?? '';

// Build the appropriate prompt
$prompt = buildImagePrompt($type, $context, $subtype);

// Check cache first
$cacheKey = md5($prompt);
$subDir   = getImageSubDir($type);
$cached   = findCachedImage($cacheKey, $subDir);

if ($cached) {
    echo json_encode(['image_url' => $cached, 'cached' => true]);
    exit;
}

// Generate new image
$imageData = callStableDiffusionApi($prompt);

if (!$imageData) {
    http_response_code(503);
    echo json_encode(['error' => 'Image generation service unavailable']);
    exit;
}

// Save to cache
$filePath = saveImage($imageData, $cacheKey, $subDir);

if (!$filePath) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save generated image']);
    exit;
}

echo json_encode(['image_url' => $filePath, 'cached' => false]);

// ─── Helper Functions ────────────────────────────────────────────────────────

/**
 * Build an image prompt based on type and context.
 */
function buildImagePrompt(string $type, string $context, string $subtype): string {
    return match ($type) {
        'scene'   => buildScenePrompt($context, $subtype),
        'npc'     => buildNpcPortraitPrompt($context, $subtype),
        'monster' => buildMonsterPrompt($context),
        'town'    => buildTownPrompt($context, $subtype),
        'dungeon' => buildDungeonRoomPrompt($context, $subtype),
        'item'    => buildItemPrompt($context),
        'map'     => buildWorldMapPrompt(),
        default   => buildScenePrompt($context, $subtype),
    };
}

/**
 * Map image type to subdirectory.
 */
function getImageSubDir(string $type): string {
    return match ($type) {
        'npc'     => 'npcs',
        'monster' => 'monsters',
        'item'    => 'items',
        'town'    => 'towns',
        'dungeon' => 'dungeons',
        'map'     => 'maps',
        default   => 'scenes',
    };
}

/**
 * Check whether a cached image exists and return its URL if so.
 */
function findCachedImage(string $cacheKey, string $subDir): ?string {
    $path = IMAGES_PATH . "/{$subDir}/{$cacheKey}.png";
    if (file_exists($path)) {
        return '/images/generated/' . $subDir . '/' . $cacheKey . '.png';
    }
    return null;
}

/**
 * Call the Stable Diffusion API.
 *
 * @param string $prompt
 * @return string|null Raw image binary data.
 */
function callStableDiffusionApi(string $prompt): ?string {
    if ($prompt === '') {
        $errorEntry = date('c') . " | SD API Error: empty prompt supplied\n";
        file_put_contents(LOGS_PATH . '/errors.log', $errorEntry, FILE_APPEND | LOCK_EX);
        return null;
    }

    // The Stability AI core endpoint requires multipart/form-data.
    // Passing an array to CURLOPT_POSTFIELDS makes curl send it that way automatically.
    $payload = [
        'prompt'        => $prompt,
        'output_format' => 'png',
    ];

    $ch = curl_init(STABLE_DIFFUSION_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . STABLE_DIFFUSION_API_KEY,
            'Accept: image/*',
        ],
        CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        $errorEntry = date('c') . " | SD API curl error: {$error}\n";
        file_put_contents(LOGS_PATH . '/errors.log', $errorEntry, FILE_APPEND | LOCK_EX);
        return null;
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        $errorEntry = date('c') . " | SD API Error: HTTP {$httpCode} | response: " . substr($response, 0, 500) . "\n";
        file_put_contents(LOGS_PATH . '/errors.log', $errorEntry, FILE_APPEND | LOCK_EX);
        return null;
    }

    return $response;
}

/**
 * Save image binary data to the cache directory.
 *
 * @param string $imageData Raw image binary.
 * @param string $cacheKey  MD5 hash of the prompt.
 * @param string $subDir    Subdirectory within images/generated.
 * @return string|null Relative URL path or null on failure.
 */
function saveImage(string $imageData, string $cacheKey, string $subDir): ?string {
    $dir = IMAGES_PATH . "/{$subDir}";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filePath = $dir . "/{$cacheKey}.png";
    if (file_put_contents($filePath, $imageData) === false) {
        return null;
    }

    return '/images/generated/' . $subDir . '/' . $cacheKey . '.png';
}
