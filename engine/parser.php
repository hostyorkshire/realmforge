<?php
/**
 * Input Parser
 * Sanitises and categorises player input actions.
 */

const ACTION_KEYWORDS = [
    'move'    => ['go', 'walk', 'travel', 'head', 'move', 'enter', 'leave', 'exit', 'climb', 'descend'],
    'combat'  => ['attack', 'fight', 'strike', 'hit', 'kill', 'slay', 'battle', 'defend'],
    'explore' => ['search', 'look', 'examine', 'inspect', 'investigate', 'explore', 'check', 'scout'],
    'talk'    => ['talk', 'speak', 'ask', 'say', 'tell', 'greet', 'chat', 'converse'],
    'trade'   => ['buy', 'sell', 'trade', 'purchase', 'shop'],
    'use'     => ['use', 'drink', 'eat', 'open', 'close', 'take', 'grab', 'pick', 'drop'],
    'rest'    => ['rest', 'sleep', 'camp', 'wait', 'sit'],
];

/**
 * Sanitise raw player input.
 *
 * @param string $input Raw player input.
 * @param int    $maxLength Maximum allowed length.
 * @return string Sanitised input.
 */
function sanitiseInput(string $input, int $maxLength = 200): string {
    $input = strip_tags($input);
    $input = trim($input);
    $input = substr($input, 0, $maxLength);
    // Remove any characters that could be prompt injection attempts
    $input = preg_replace('/[^\p{L}\p{N}\s\'\-\.,!?]/u', '', $input);
    return $input;
}

/**
 * Classify an action into a category.
 * Uses whole-word matching to avoid false positives from substrings.
 *
 * @param string $action Sanitised action string.
 * @return string Action category (move, combat, explore, talk, trade, use, rest, general).
 */
function classifyAction(string $action): string {
    $actionLower = strtolower($action);
    // Split into individual words for exact matching
    $words = preg_split('/\s+/', $actionLower, -1, PREG_SPLIT_NO_EMPTY);
    foreach (ACTION_KEYWORDS as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (in_array($keyword, $words, true)) {
                return $category;
            }
        }
    }
    return 'general';
}

/**
 * Validate that an action is suitable to pass to the AI.
 *
 * @param string $action Sanitised action.
 * @return bool
 */
function isValidAction(string $action): bool {
    if (strlen(trim($action)) < 2) {
        return false;
    }
    // Block empty or purely numeric input
    if (is_numeric(trim($action))) {
        return false;
    }
    return true;
}
