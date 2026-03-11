<?php
/**
 * Town System
 */

const TOWN_DEFINITIONS = [
    'stonebridge_village' => [
        'id'       => 'stonebridge_village',
        'name'     => 'Stonebridge',
        'type'     => 'village',
        'biome'    => 'plains',
        'x'        => 15,
        'y'        => 21,
        'faction'  => 'kingdom_avaros',
        'buildings' => ['Tavern', 'Blacksmith', 'Inn', 'Marketplace'],
        'description' => 'A modest village on the banks of the River Stone, known for its old stone bridge.',
    ],
    'ravenmoor_town' => [
        'id'       => 'ravenmoor_town',
        'name'     => 'Ravenmoor',
        'type'     => 'town',
        'biome'    => 'swamp',
        'x'        => 28,
        'y'        => 33,
        'faction'  => 'shadow_brotherhood',
        'buildings' => ['Tavern', 'Marketplace', 'Magic Shop', 'Inn'],
        'description' => 'A dark, mist-shrouded town at the edge of the Ravenmoor Swamp. Strange things happen here.',
    ],
    'ironpeak_settlement' => [
        'id'       => 'ironpeak_settlement',
        'name'     => 'Ironpeak',
        'type'     => 'settlement',
        'biome'    => 'mountains',
        'x'        => 38,
        'y'        => 12,
        'faction'  => 'iron_dominion',
        'buildings' => ['Blacksmith', 'Marketplace', 'Temple', 'Inn'],
        'description' => 'A fortified settlement carved into the mountains, mining iron ore for the Iron Dominion.',
    ],
    'dawn_harbour' => [
        'id'       => 'dawn_harbour',
        'name'     => 'Dawn Harbour',
        'type'     => 'port',
        'biome'    => 'coast',
        'x'        => 8,
        'y'        => 40,
        'faction'  => 'temple_of_dawn',
        'buildings' => ['Tavern', 'Marketplace', 'Temple', 'Inn', 'Magic Shop'],
        'description' => 'A bustling coastal port where traders and pilgrims of the Temple of Dawn converge.',
    ],
];

/**
 * Get a town by ID.
 *
 * @param string $townId
 * @return array|null
 */
function getTown(string $townId): ?array {
    return TOWN_DEFINITIONS[$townId] ?? null;
}

/**
 * Get all towns.
 *
 * @return array
 */
function getAllTowns(): array {
    return TOWN_DEFINITIONS;
}

/**
 * Get towns by biome.
 *
 * @param string $biome
 * @return array
 */
function getTownsByBiome(string $biome): array {
    return array_values(array_filter(TOWN_DEFINITIONS, fn($t) => $t['biome'] === $biome));
}

/**
 * Generate tavern events for a location.
 *
 * @return array List of event strings.
 */
function getTavernEvents(): array {
    $events = [
        'A grizzled mercenary in the corner is muttering about dragon sightings to the north.',
        'Two merchants argue loudly about a missing shipment on the Ironpeak road.',
        'A hooded stranger slips a folded note under your tankard and disappears.',
        'The innkeeper lowers their voice: "Bandits hit the Crown convoy last night."',
        'A bard plays a mournful tune about the old empire, drawing tearful listeners.',
        'A fight breaks out between a soldier and a local farmer. The crowd takes sides.',
        'A young courier bursts in, breathless: "The eastern gate is under attack!"',
    ];
    return $events;
}
