<?php
/**
 * NPC Dialogue API Endpoint
 * Returns context-aware NPC dialogue using the Groq API.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/npcs.php';
require_once __DIR__ . '/../../engine/parser.php';

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

$npcId       = $body['npc_id']   ?? '';
$playerInput = sanitiseInput($body['input'] ?? 'greet');
$state       = $body['state']    ?? [];

$npc = getNpc($npcId);
if (!$npc) {
    http_response_code(404);
    echo json_encode(['error' => 'NPC not found']);
    exit;
}

// Build NPC dialogue prompt
$systemPrompt = "You are {$npc['name']}, a {$npc['role']} in the fantasy world of Aeloria. " .
                "Your personality is {$npc['personality']}. " .
                "Respond in character to the player's message. Keep your response to 2-3 sentences. " .
                "Stay true to your personality and knowledge level as a {$npc['role']}.";

$userPrompt = "The player says: \"{$playerInput}\"";

$response = callGroqDialogue($systemPrompt, $userPrompt);

if (!$response) {
    // Fall back to static dialogue
    $response = getNpcDialogue($npcId, 'greeting');
}

echo json_encode([
    'npc'      => $npc['name'],
    'dialogue' => $response,
]);

/**
 * Call Groq for NPC dialogue generation.
 */
function callGroqDialogue(string $systemPrompt, string $userPrompt): ?string {
    $payload = json_encode([
        'model'       => GROQ_MODEL,
        'messages'    => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ],
        'max_tokens'  => 100,
        'temperature' => 0.7,
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
        error_log('Groq NPC dialogue curl error: ' . $error);
        return null;
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? null;
}
