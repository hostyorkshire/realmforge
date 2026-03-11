<?php
/**
 * NPC System
 */

const NPC_DEFINITIONS = [
    'borin_blacksmith' => [
        'id'          => 'borin_blacksmith',
        'name'        => 'Borin the Blacksmith',
        'location'    => 'stonebridge_village',
        'personality' => 'grumpy but loyal',
        'role'        => 'blacksmith',
        'dialogue'    => [
            'greeting'  => "What do ye want? I'm busy.",
            'trade'     => "Fine, take a look at me wares. But don't touch what ye can't afford.",
            'quest'     => "Aye, I heard the bandits took the King's crown. Fools the lot of them.",
        ],
    ],
    'elara_innkeeper' => [
        'id'          => 'elara_innkeeper',
        'name'        => 'Elara the Innkeeper',
        'location'    => 'stonebridge_village',
        'personality' => 'warm and gossipy',
        'role'        => 'innkeeper',
        'dialogue'    => [
            'greeting'  => "Welcome, traveller! Come in, come in. The fire is warm and the ale is cold.",
            'rumour'    => "Oh, have you heard? A merchant went missing on the Ironpeak road last week.",
            'room'      => "A room for the night? That'll be 5 gold. Sleep well, you look exhausted.",
        ],
    ],
    'theron_guardcaptain' => [
        'id'          => 'theron_guardcaptain',
        'name'        => 'Captain Theron',
        'location'    => 'ravenmoor_town',
        'personality' => 'stern and duty-bound',
        'role'        => 'guard captain',
        'dialogue'    => [
            'greeting'  => "State your business in Ravenmoor.",
            'warning'   => "The northern roads are dangerous. Travel in groups if you value your life.",
            'quest'     => "We need someone to investigate the ruins east of town. Too many guards have gone missing.",
        ],
    ],
    'mira_sorceress' => [
        'id'          => 'mira_sorceress',
        'name'        => 'Mira the Sorceress',
        'location'    => 'ironpeak_settlement',
        'personality' => 'mysterious and cryptic',
        'role'        => 'mage',
        'dialogue'    => [
            'greeting'  => "I sensed your arrival before you knocked. The stars speak of interesting times.",
            'magic'     => "You seek knowledge of the arcane? Knowledge has a price, traveller.",
            'prophecy'  => "The relic stirs. The Brotherhood moves. Be wary of those who smile too wide.",
        ],
    ],
];

/**
 * Get an NPC by ID.
 *
 * @param string $npcId
 * @return array|null
 */
function getNpc(string $npcId): ?array {
    return NPC_DEFINITIONS[$npcId] ?? null;
}

/**
 * Get all NPCs at a given location.
 *
 * @param string $location Location ID.
 * @return array List of NPC definitions.
 */
function getNpcsAtLocation(string $location): array {
    return array_values(array_filter(NPC_DEFINITIONS, fn($npc) => $npc['location'] === $location));
}

/**
 * Get a random dialogue line from an NPC.
 *
 * @param string $npcId   NPC identifier.
 * @param string $context Optional dialogue key.
 * @return string Dialogue text.
 */
function getNpcDialogue(string $npcId, string $context = 'greeting'): string {
    $npc = getNpc($npcId);
    if (!$npc) {
        return "...";
    }
    $dialogue = $npc['dialogue'];
    return $dialogue[$context] ?? $dialogue['greeting'] ?? "...";
}

/**
 * Return all NPC definitions.
 *
 * @return array
 */
function getAllNpcs(): array {
    return NPC_DEFINITIONS;
}
