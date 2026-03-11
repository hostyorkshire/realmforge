<?php
/**
 * Continent Generator
 * Procedurally generates the world on first launch.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/world.php';
require_once __DIR__ . '/towns.php';
require_once __DIR__ . '/dungeons.php';

/**
 * Generate the full world and save it to database/world.json.
 *
 * @return array The generated world data.
 */
function generateWorld(): array {
    $size = WORLD_GRID_SIZE; // 50 x 50

    $tiles    = generateTiles($size);
    $kingdoms = assignKingdoms($tiles, $size);
    $towns    = generateTownPlacements();
    $dungeons = generateDungeonPlacements();
    $roads    = generateRoadNetwork($towns);

    $world = [
        'version'   => 1,
        'generated' => date('c'),
        'size'      => $size,
        'tiles'     => $tiles,
        'kingdoms'  => array_values(KINGDOM_DEFINITIONS),
        'towns'     => $towns,
        'dungeons'  => $dungeons,
        'roads'     => $roads,
    ];

    saveWorld($world);

    return $world;
}

/**
 * Generate the tile grid using simplex-like pseudo-noise.
 *
 * @param int $size Grid dimension (size x size).
 * @return array Flat array of tile definitions.
 */
function generateTiles(int $size): array {
    $tiles = [];

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $tiles[] = [
                'x'         => $x,
                'y'         => $y,
                'biome'     => selectBiome($x, $y, $size),
                'elevation' => selectElevation($x, $y, $size),
                'region'    => null,
                'location'  => null,
            ];
        }
    }

    return $tiles;
}

/**
 * Select a biome for a given coordinate using deterministic pseudo-noise.
 * The multipliers (374761393 and 668265263) are large primes chosen to
 * produce well-distributed hash values across the grid.
 *
 * @param int $x    X coordinate.
 * @param int $y    Y coordinate.
 * @param int $size Grid size.
 * @return string Biome name.
 */
function selectBiome(int $x, int $y, int $size): string {
    // Large prime multipliers for deterministic pseudo-random hashing
    $hash = (($x * 374761393) + ($y * 668265263)) & 0x7FFFFFFF;
    $normalized = $hash / 0x7FFFFFFF;

    // Coast on edges
    if ($x < 3 || $x > $size - 4 || $y < 3 || $y > $size - 4) {
        return 'coast';
    }

    // Mountains in upper section
    if ($y < $size * 0.2) {
        return $normalized < 0.6 ? 'mountains' : 'forest';
    }

    // Desert in right portion
    if ($x > $size * 0.7 && $y > $size * 0.3) {
        return $normalized < 0.5 ? 'desert' : 'plains';
    }

    // Swamp in lower-left
    if ($x < $size * 0.3 && $y > $size * 0.6) {
        return $normalized < 0.4 ? 'swamp' : 'forest';
    }

    // Default: mix of forest and plains
    $biomes = ['forest', 'forest', 'plains', 'plains', 'forest'];
    return $biomes[$hash % count($biomes)];
}

/**
 * Select elevation for a tile.
 *
 * @param int $x    X coordinate.
 * @param int $y    Y coordinate.
 * @param int $size Grid size.
 * @return int Elevation 1-5.
 */
function selectElevation(int $x, int $y, int $size): int {
    $hash = (($x * 1234567) ^ ($y * 7654321)) & 0x7FFFFFFF;
    // Higher elevation in the north/mountains region
    if ($y < $size * 0.25) {
        return ($hash % 3) + 3; // 3-5
    }
    return ($hash % 3) + 1; // 1-3
}

/**
 * Assign tiles to kingdoms based on proximity to kingdom centres.
 *
 * @param array $tiles Tile array (passed by reference).
 * @param int   $size  Grid size.
 * @return array Modified tiles.
 */
function assignKingdoms(array $tiles, int $size): array {
    foreach ($tiles as &$tile) {
        $tile['region'] = getNearestKingdom($tile['x'], $tile['y']);
    }
    unset($tile);
    return $tiles;
}

/**
 * Find the nearest kingdom ID for a given coordinate.
 *
 * @param int $x
 * @param int $y
 * @return string Kingdom ID.
 */
function getNearestKingdom(int $x, int $y): string {
    $nearest  = null;
    $minDist  = PHP_INT_MAX;

    foreach (KINGDOM_DEFINITIONS as $id => $kingdom) {
        $dx   = $x - $kingdom['center']['x'];
        $dy   = $y - $kingdom['center']['y'];
        $dist = sqrt($dx * $dx + $dy * $dy);
        if ($dist < $minDist) {
            $minDist = $dist;
            $nearest = $id;
        }
    }

    return $nearest ?? 'kingdom_avaros';
}

/**
 * Generate town placement data from the predefined town definitions.
 *
 * @return array
 */
function generateTownPlacements(): array {
    return array_map(function ($town) {
        return [
            'id'      => $town['id'],
            'name'    => $town['name'],
            'x'       => $town['x'],
            'y'       => $town['y'],
            'faction' => $town['faction'],
            'biome'   => $town['biome'],
            'type'    => $town['type'],
        ];
    }, array_values(TOWN_DEFINITIONS));
}

/**
 * Generate dungeon placement data from the predefined dungeon definitions.
 *
 * @return array
 */
function generateDungeonPlacements(): array {
    return array_map(function ($dungeon) {
        return [
            'id'   => $dungeon['id'],
            'name' => $dungeon['name'],
            'x'    => $dungeon['x'],
            'y'    => $dungeon['y'],
            'type' => $dungeon['type'],
        ];
    }, array_values(DUNGEON_DEFINITIONS));
}

/**
 * Generate a road network connecting towns using nearest-neighbour paths.
 *
 * @param array $towns Town placement array.
 * @return array List of road segments ['from', 'to'].
 */
function generateRoadNetwork(array $towns): array {
    $roads   = [];
    $visited = [];

    foreach ($towns as $town) {
        $nearest = null;
        $minDist = PHP_INT_MAX;

        foreach ($towns as $other) {
            if ($other['id'] === $town['id'] || in_array($other['id'], $visited, true)) {
                continue;
            }
            $dx   = $town['x'] - $other['x'];
            $dy   = $town['y'] - $other['y'];
            $dist = sqrt($dx * $dx + $dy * $dy);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $other;
            }
        }

        if ($nearest) {
            $roads[] = ['from' => $town['id'], 'to' => $nearest['id']];
        }

        $visited[] = $town['id'];
    }

    return $roads;
}
