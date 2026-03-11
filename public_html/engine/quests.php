<?php
/**
 * Quest System
 */

const QUEST_DEFINITIONS = [
    'lost_crown' => [
        'id'          => 'lost_crown',
        'title'       => 'The Lost Crown',
        'description' => 'Recover the stolen crown of King Aldric from the bandits in Darkwood Forest.',
        'reward_gold' => 100,
        'reward_item' => 'Crown of Aldric',
        'faction'     => 'kingdom_avaros',
    ],
    'dragon_rumor' => [
        'id'          => 'dragon_rumor',
        'title'       => 'Rumours of the Dragon',
        'description' => 'Investigate rumours of a dragon terrorising the northern settlements.',
        'reward_gold' => 200,
        'reward_item' => 'Dragon Scale',
        'faction'     => 'kingdom_avaros',
    ],
    'ancient_relic' => [
        'id'          => 'ancient_relic',
        'title'       => 'The Ancient Relic',
        'description' => 'Retrieve the Relic of Ages from the Crypt of Shadows before the Shadow Brotherhood claim it.',
        'reward_gold' => 150,
        'reward_item' => 'Relic of Ages',
        'faction'     => 'temple_of_dawn',
    ],
    'missing_merchant' => [
        'id'          => 'missing_merchant',
        'title'       => 'The Missing Merchant',
        'description' => 'A merchant vanished on the road to Ironpeak. Find out what happened.',
        'reward_gold' => 75,
        'reward_item' => 'Merchant\'s Ledger',
        'faction'     => 'iron_dominion',
    ],
];

/**
 * Add a quest to the player's active quest list.
 *
 * @param array  $state   Player state array.
 * @param string $questId Quest ID from QUEST_DEFINITIONS.
 * @return array Updated player state.
 */
function addQuest(array $state, string $questId): array {
    if (!isset(QUEST_DEFINITIONS[$questId])) {
        return $state;
    }
    if (!isset($state['quests'])) {
        $state['quests'] = [];
    }
    // Avoid duplicate quests
    foreach ($state['quests'] as $q) {
        if ($q['id'] === $questId) {
            return $state;
        }
    }
    $state['quests'][] = QUEST_DEFINITIONS[$questId];
    return $state;
}

/**
 * Complete a quest, awarding gold and item.
 *
 * @param array  $state   Player state array.
 * @param string $questId Quest ID to complete.
 * @return array Updated player state.
 */
function completeQuest(array $state, string $questId): array {
    require_once __DIR__ . '/inventory.php';

    $quests = $state['quests'] ?? [];
    foreach ($quests as $index => $quest) {
        if ($quest['id'] === $questId) {
            $state['gold'] = ($state['gold'] ?? 0) + $quest['reward_gold'];
            $state = addItem($state, $quest['reward_item']);
            array_splice($state['quests'], $index, 1);
            break;
        }
    }
    return $state;
}

/**
 * Return all available quest definitions.
 *
 * @return array
 */
function getAllQuests(): array {
    return QUEST_DEFINITIONS;
}
