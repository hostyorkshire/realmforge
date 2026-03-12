<?php
/**
 * Adventure API Endpoint
 * Receives a player action and returns an AI-narrated story response with choices.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/parser.php';
require_once __DIR__ . '/../../engine/gameMasterPrompt.php';
require_once __DIR__ . '/../../engine/history.php';
require_once __DIR__ . '/../../engine/memory.php';
require_once __DIR__ . '/../../engine/combat.php';
require_once __DIR__ . '/../../engine/dice.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Parse JSON body
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$rawAction   = $body['action'] ?? '';
$playerState = $body['state']  ?? [];

// Sanitise and validate input
$action = sanitiseInput($rawAction);

if (!isValidAction($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action provided']);
    exit;
}

// Initialise state defaults
$playerState = array_merge([
    'location'           => 'stonebridge_village',
    'health'             => 100,
    'gold'               => 25,
    'inventory'          => ['Torch'],
    'quests'             => [],
    'faction_reputation' => [],
    'history'            => [],
    'story_summary'      => '',
], $playerState);

// Log player action
logPlayerAction($action, $playerState['location'] ?? 'unknown');

// Compress memory if history is full
if (count($playerState['history']) >= MAX_HISTORY_EVENTS) {
    $playerState = compressStoryMemory($playerState);
}

// Process special action types
$actionCategory = classifyAction($action);
$extraContext   = '';

if ($actionCategory === 'combat') {
    $combatResult    = attackMonster($playerState);
    $playerState     = $combatResult['state'];
    $extraContext    = $combatResult['result'];
}

// Build and send prompt to Groq
$messages = buildGameMasterPrompt($playerState, $action);
$response = callGroqApi($messages);

if (!$response) {
    http_response_code(503);
    echo json_encode(['error' => 'AI service unavailable. Please try again.']);
    exit;
}

// Parse response into narrative and choices
$parsed = parseAiResponse($response);

// Add event to history
$playerState = addEvent($playerState, "Action: {$action}. " . substr($parsed['narrative'], 0, 100) . '...');

// Return response
echo json_encode([
    'narrative'   => $parsed['narrative'],
    'choices'     => $parsed['choices'],
    'state'       => $playerState,
    'combat_info' => $extraContext ?: null,
]);

// ─── Helper Functions ───────────────────────────────────────────────────────

/**
 * Call the Groq API with the given messages.
 *
 * @param array $messages
 * @return string|null AI response text.
 */
function callGroqApi(array $messages): ?string {
    $payload = json_encode([
        'model'       => GROQ_MODEL,
        'messages'    => $messages,
        'max_tokens'  => 300,
        'temperature' => 0.8,
    ]);

    $ch = curl_init(GROQ_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log('Groq API curl error: ' . $error);
        logAiRequest($payload, 0);
        return null;
    }
    curl_close($ch);

    logAiRequest($payload, $httpCode);

    if ($httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? null;
}

/**
 * Parse the AI response into narrative text and choices array.
 *
 * @param string $response Raw AI response.
 * @return array ['narrative' => string, 'choices' => string[]]
 */
function parseAiResponse(string $response): array {
    $lines     = explode("\n", trim($response));
    $choices   = [];
    $narrative = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'CHOICE:')) {
            $choices[] = trim(substr($line, 7));
        } elseif (!empty($line)) {
            $narrative[] = $line;
        }
    }

    // Ensure exactly 3 choices
    $defaultChoices = ['Look around carefully', 'Continue along the road', 'Rest and recover'];
    while (count($choices) < 3) {
        $choices[] = $defaultChoices[count($choices)] ?? 'Wait and observe';
    }
    $choices = array_slice($choices, 0, 3);

    return [
        'narrative' => implode(' ', $narrative),
        'choices'   => $choices,
    ];
}

/**
 * Log an AI API request.
 *
 * @param string $payload  Request payload.
 * @param int    $httpCode Response code.
 */
function logAiRequest(string $payload, int $httpCode): void {
    $logFile = LOGS_PATH . '/ai_requests.log';
    $entry   = date('c') . " | HTTP {$httpCode} | " . substr($payload, 0, 200) . "\n";
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Log a player action.
 *
 * @param string $action   Player action.
 * @param string $location Current location.
 */
function logPlayerAction(string $action, string $location): void {
    $logFile = LOGS_PATH . '/player_actions.log';
    $entry   = date('c') . " | {$location} | {$action}\n";
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}
