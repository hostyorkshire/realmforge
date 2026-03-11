<?php
/**
 * Faction System
 */

const FACTION_DEFINITIONS = [
    'kingdom_avaros' => [
        'id'          => 'kingdom_avaros',
        'name'        => 'Kingdom of Avaros',
        'description' => 'The ruling kingdom of the central plains, known for its knights and fair laws.',
        'alignment'   => 'lawful good',
        'color'       => 'gold',
    ],
    'iron_dominion' => [
        'id'          => 'iron_dominion',
        'name'        => 'Iron Dominion',
        'description' => 'A militaristic empire from the eastern mountains, expanding through conquest.',
        'alignment'   => 'lawful evil',
        'color'       => 'grey',
    ],
    'northern_clans' => [
        'id'          => 'northern_clans',
        'name'        => 'Northern Clans',
        'description' => 'Fierce warriors from the frozen north who value strength and honour above all.',
        'alignment'   => 'chaotic neutral',
        'color'       => 'blue',
    ],
    'shadow_brotherhood' => [
        'id'          => 'shadow_brotherhood',
        'name'        => 'Shadow Brotherhood',
        'description' => 'A secretive thieves guild operating in the shadows of every city.',
        'alignment'   => 'chaotic evil',
        'color'       => 'purple',
    ],
    'temple_of_dawn' => [
        'id'          => 'temple_of_dawn',
        'name'        => 'Temple of Dawn',
        'description' => 'A powerful religious order devoted to the sun goddess, seeking justice and healing.',
        'alignment'   => 'neutral good',
        'color'       => 'white',
    ],
];

/**
 * Get or initialise faction reputation for a player.
 *
 * @param array $state Player state.
 * @return array Updated player state with all factions at 0 if not set.
 */
function initialiseFactionReputation(array $state): array {
    if (!isset($state['faction_reputation'])) {
        $state['faction_reputation'] = [];
    }
    foreach (FACTION_DEFINITIONS as $id => $_) {
        if (!isset($state['faction_reputation'][$id])) {
            $state['faction_reputation'][$id] = 0;
        }
    }
    return $state;
}

/**
 * Adjust faction reputation by the given amount.
 *
 * @param array  $state    Player state.
 * @param string $faction  Faction ID.
 * @param int    $amount   Positive or negative reputation change.
 * @return array Updated player state.
 */
function adjustReputation(array $state, string $faction, int $amount): array {
    $state = initialiseFactionReputation($state);
    if (isset($state['faction_reputation'][$faction])) {
        $state['faction_reputation'][$faction] = max(-100, min(100, $state['faction_reputation'][$faction] + $amount));
    }
    return $state;
}

/**
 * Get the player's reputation tier with a faction.
 *
 * @param array  $state   Player state.
 * @param string $faction Faction ID.
 * @return string Reputation tier label.
 */
function getReputationTier(array $state, string $faction): string {
    $rep = $state['faction_reputation'][$faction] ?? 0;
    if ($rep >= 75)  return 'Revered';
    if ($rep >= 50)  return 'Honoured';
    if ($rep >= 25)  return 'Friendly';
    if ($rep >= 0)   return 'Neutral';
    if ($rep >= -25) return 'Unfriendly';
    if ($rep >= -50) return 'Hostile';
    return 'Hated';
}

/**
 * Return all faction definitions.
 *
 * @return array
 */
function getAllFactions(): array {
    return FACTION_DEFINITIONS;
}
