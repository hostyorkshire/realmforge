<?php
/**
 * History & Event System
 * Manages the player's recent event history, capping at MAX_HISTORY_EVENTS.
 */

require_once __DIR__ . '/../config.php';

/**
 * Add an event to the player state history.
 * If the history exceeds MAX_HISTORY_EVENTS, older events are trimmed
 * (compression is handled separately via memory.php).
 *
 * @param array  $state Reference to the player state array.
 * @param string $event Narrative description of the event.
 * @return array Updated player state.
 */
function addEvent(array $state, string $event): array {
    if (!isset($state['history'])) {
        $state['history'] = [];
    }

    $state['history'][] = $event;

    // Keep only the most recent events; older ones feed story_summary via compressStoryMemory
    if (count($state['history']) > MAX_HISTORY_EVENTS) {
        $state['history'] = array_slice($state['history'], -MAX_HISTORY_EVENTS);
    }

    return $state;
}

/**
 * Return the full history as a single formatted string.
 *
 * @param array $state Player state array.
 * @return string Newline-separated history entries.
 */
function getHistoryText(array $state): string {
    $history = $state['history'] ?? [];
    return implode("\n", $history);
}
