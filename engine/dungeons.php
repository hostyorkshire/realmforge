<?php
/**
 * Dungeon System
 */

require_once __DIR__ . '/dice.php';

const DUNGEON_DEFINITIONS = [
    'crypt_of_shadows' => [
        'id'          => 'crypt_of_shadows',
        'name'        => 'Crypt of Shadows',
        'x'           => 28,
        'y'           => 17,
        'type'        => 'crypt',
        'description' => 'An ancient burial ground said to hold the Relic of Ages.',
        'difficulty'  => 3,
    ],
    'goblin_cave' => [
        'id'          => 'goblin_cave',
        'name'        => 'Goblin Cave',
        'x'           => 20,
        'y'           => 25,
        'type'        => 'cave',
        'description' => 'A network of tunnels overrun with goblin clans.',
        'difficulty'  => 1,
    ],
    'forgotten_catacombs' => [
        'id'          => 'forgotten_catacombs',
        'name'        => 'Forgotten Catacombs',
        'x'           => 42,
        'y'           => 30,
        'type'        => 'catacombs',
        'description' => 'Labyrinthine tunnels beneath an ancient ruined city.',
        'difficulty'  => 2,
    ],
    'dragon_lair' => [
        'id'          => 'dragon_lair',
        'name'        => 'Dragon\'s Lair',
        'x'           => 45,
        'y'           => 8,
        'type'        => 'lair',
        'description' => 'A volcanic cavern in the northern mountains where a great dragon dwells.',
        'difficulty'  => 5,
    ],
];

const ROOM_TYPES = ['entrance', 'combat', 'treasure', 'trap', 'puzzle', 'boss'];

const MONSTERS_BY_DUNGEON_TYPE = [
    'crypt'     => ['Skeleton Warrior', 'Zombie', 'Ghost', 'Lich'],
    'cave'      => ['Goblin', 'Cave Troll', 'Giant Spider', 'Bat Swarm'],
    'catacombs' => ['Undead Soldier', 'Wraith', 'Stone Golem', 'Vampire Spawn'],
    'lair'      => ['Dragon Cultist', 'Fire Drake', 'Dragon', 'Dragon Whelp'],
];

/**
 * Generate a dungeon room layout for a given dungeon.
 *
 * @param string $dungeonId Dungeon identifier.
 * @param int    $rooms     Number of rooms to generate.
 * @return array List of room definitions.
 */
function generateDungeonRooms(string $dungeonId, int $rooms = 6): array {
    $dungeon = DUNGEON_DEFINITIONS[$dungeonId] ?? null;
    if (!$dungeon) {
        return [];
    }

    $dungeonRooms = [];
    $type         = $dungeon['type'];
    $monsters     = MONSTERS_BY_DUNGEON_TYPE[$type] ?? ['Monster'];

    for ($i = 0; $i < $rooms; $i++) {
        $roomType = $i === 0 ? 'entrance' : ($i === $rooms - 1 ? 'boss' : ROOM_TYPES[array_rand(ROOM_TYPES)]);

        $room = [
            'room_id' => $dungeonId . sprintf('%02d', $i + 1),
            'type'    => $roomType,
            'exits'   => [],
        ];

        if ($roomType === 'combat' || $roomType === 'boss') {
            $room['monster'] = $monsters[array_rand($monsters)];
        }

        if ($roomType === 'treasure') {
            $room['loot'] = 'Gold Coin';
        }

        if ($roomType === 'trap') {
            $room['damage'] = rollDice(6);
        }

        // Link rooms linearly
        if ($i > 0) {
            $room['exits']['north'] = $dungeonId . sprintf('%02d', $i);
        }
        if ($i < $rooms - 1) {
            $room['exits']['south'] = $dungeonId . sprintf('%02d', $i + 2);
        }

        $dungeonRooms[] = $room;
    }

    return $dungeonRooms;
}

/**
 * Get a dungeon by ID.
 *
 * @param string $dungeonId
 * @return array|null
 */
function getDungeon(string $dungeonId): ?array {
    return DUNGEON_DEFINITIONS[$dungeonId] ?? null;
}

/**
 * Get all dungeon definitions.
 *
 * @return array
 */
function getAllDungeons(): array {
    return DUNGEON_DEFINITIONS;
}
