<?php
/**
 * AI Game Master Prompt Builder
 * Assembles the complete system + user prompt for the Groq API.
 */

require_once __DIR__ . '/lore.php';

/**
 * Build the full prompt payload for the Groq API.
 *
 * @param array  $state  Player state array.
 * @param string $action The player's chosen action.
 * @return array Messages array ready for Groq API.
 */
function buildGameMasterPrompt(array $state, string $action): array {
    $systemPrompt = buildSystemPrompt();
    $userPrompt   = buildUserPrompt($state, $action);

    return [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userPrompt],
    ];
}

/**
 * Build the system rules prompt.
 *
 * @return string
 */
function buildSystemPrompt(): string {
    $lore = getLoreSummary();

    return <<<PROMPT
You are the Game Master of RealmForge, a dark fantasy RPG set in the continent of Aeloria.

WORLD LORE:
{$lore}

RULES YOU MUST FOLLOW:
1. Narrate events in vivid, atmospheric prose. Use 80-120 words only.
2. You MUST NOT control the player character or make decisions for them.
3. You MUST NOT add items to or remove items from the player's inventory.
4. You MUST NOT invent map locations not consistent with the established world.
5. You MUST NOT change the player's stats (health, gold) directly.
6. Write in second person ("You see...", "You hear...", "Before you stands...").
7. Focus on atmosphere, tension, and narrative consequence.
8. At the end of your response, provide EXACTLY 3 suggested actions on separate lines, each prefixed with "CHOICE:".

FORMAT:
[80-120 word narrative]

CHOICE: [action 1]
CHOICE: [action 2]
CHOICE: [action 3]
PROMPT;
}

/**
 * Build the user context prompt.
 *
 * @param array  $state  Player state array.
 * @param string $action Player's action.
 * @return string
 */
function buildUserPrompt(array $state, string $action): string {
    $location    = $state['location']    ?? 'unknown location';
    $health      = $state['health']      ?? 100;
    $gold        = $state['gold']        ?? 0;
    $inventory   = implode(', ', $state['inventory'] ?? ['nothing']);
    $quests      = array_column($state['quests'] ?? [], 'title');
    $questsText  = empty($quests) ? 'none' : implode(', ', $quests);
    $storySummary = $state['story_summary'] ?? '';
    $recentEvents = implode("\n", array_slice($state['history'] ?? [], -3));

    $factionRep = '';
    if (!empty($state['faction_reputation'])) {
        $parts = [];
        foreach ($state['faction_reputation'] as $faction => $rep) {
            $parts[] = "{$faction}: {$rep}";
        }
        $factionRep = implode(', ', $parts);
    }

    $prompt = "PLAYER SITUATION:\n";
    $prompt .= "Location: {$location}\n";
    $prompt .= "Health: {$health} HP\n";
    $prompt .= "Gold: {$gold}\n";
    $prompt .= "Inventory: {$inventory}\n";
    $prompt .= "Active Quests: {$questsText}\n";

    if ($factionRep) {
        $prompt .= "Faction Standing: {$factionRep}\n";
    }

    if ($storySummary) {
        $prompt .= "\nSTORY SO FAR:\n{$storySummary}\n";
    }

    if ($recentEvents) {
        $prompt .= "\nRECENT EVENTS:\n{$recentEvents}\n";
    }

    $prompt .= "\nPLAYER ACTION: {$action}";

    return $prompt;
}
