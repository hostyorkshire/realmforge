<?php
/**
 * Memory Compression System
 * Summarises older events via the Groq API to prevent AI token overflow.
 */

require_once __DIR__ . '/../config.php';

/**
 * Compress the player's story history into a short summary.
 *
 * Collects older events, sends them to Groq, and stores the resulting
 * summary in $state['story_summary']. The history array is then cleared
 * so fresh events can accumulate again.
 *
 * @param array $state Player state array (passed by reference).
 * @return array Updated player state with compressed story_summary.
 */
function compressStoryMemory(array $state): array {
    $history = $state['history'] ?? [];

    if (empty($history)) {
        return $state;
    }

    $eventsText = implode("\n", $history);

    $prompt = "Summarise the following RPG adventure events in 2-3 concise sentences suitable for " .
              "a continuing story context. Focus on key plot points, locations visited, and notable " .
              "encounters. Events:\n\n" . $eventsText;

    $summary = callGroqForSummary($prompt);

    if ($summary) {
        // Append to existing summary if present
        $existing = $state['story_summary'] ?? '';
        $state['story_summary'] = trim($existing . ' ' . $summary);
        $state['history'] = [];
    }

    return $state;
}

/**
 * Send a summarisation request to Groq and return the result.
 *
 * @param string $prompt The prompt to send.
 * @return string|null Summary text or null on failure.
 */
function callGroqForSummary(string $prompt): ?string {
    $payload = json_encode([
        'model'    => GROQ_MODEL,
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
        ],
        'max_tokens' => 150,
        'temperature' => 0.3,
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
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? null;
}
