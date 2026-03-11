<?php
/**
 * World Engine
 * Manages the world map, tile lookups, and kingdom data.
 */

require_once __DIR__ . '/../config.php';

const BIOMES = ['forest', 'plains', 'mountains', 'desert', 'swamp', 'coast'];

const KINGDOM_DEFINITIONS = [
    'kingdom_avaros' => [
        'id'     => 'kingdom_avaros',
        'name'   => 'Kingdom of Avaros',
        'center' => ['x' => 15, 'y' => 25],
        'color'  => 'gold',
    ],
    'iron_dominion' => [
        'id'     => 'iron_dominion',
        'name'   => 'Iron Dominion',
        'center' => ['x' => 38, 'y' => 15],
        'color'  => 'grey',
    ],
    'northern_clans' => [
        'id'     => 'northern_clans',
        'name'   => 'Northern Clans',
        'center' => ['x' => 25, 'y' => 5],
        'color'  => 'blue',
    ],
];

/**
 * Load the world data from disk.
 *
 * @return array|null World data or null if not generated yet.
 */
function loadWorld(): ?array {
    if (!file_exists(WORLD_FILE)) {
        return null;
    }
    $json = file_get_contents(WORLD_FILE);
    return json_decode($json, true);
}

/**
 * Save world data to disk.
 *
 * @param array $world World data to save.
 * @return bool Success.
 */
function saveWorld(array $world): bool {
    $dir = dirname(WORLD_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents(WORLD_FILE, json_encode($world, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Get a tile from the world grid.
 *
 * @param array $world World data.
 * @param int   $x     X coordinate.
 * @param int   $y     Y coordinate.
 * @return array|null Tile data or null.
 */
function getTile(array $world, int $x, int $y): ?array {
    foreach ($world['tiles'] ?? [] as $tile) {
        if ($tile['x'] === $x && $tile['y'] === $y) {
            return $tile;
        }
    }
    return null;
}

/**
 * Get all kingdom definitions.
 *
 * @return array
 */
function getKingdoms(): array {
    return KINGDOM_DEFINITIONS;
}
