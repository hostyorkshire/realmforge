<?php
/**
 * Image Prompt Generator
 * Builds Stable Diffusion prompts for various game contexts.
 */

/**
 * Generate an image prompt for a story scene.
 *
 * @param string $location  Location name.
 * @param string $situation Brief scene description.
 * @return string
 */
function buildScenePrompt(string $location, string $situation): string {
    return "fantasy RPG scene, {$location}, {$situation}, " .
           "dramatic lighting, dark fantasy art style, cinematic composition, " .
           "highly detailed, digital painting, matte painting, 8k resolution";
}

/**
 * Generate an image prompt for an NPC portrait.
 *
 * @param string $name        NPC name.
 * @param string $description NPC description or personality.
 * @return string
 */
function buildNpcPortraitPrompt(string $name, string $description): string {
    return "fantasy RPG character portrait, {$name}, {$description}, " .
           "dramatic lighting, dark fantasy style, detailed face, " .
           "professional digital art, high quality";
}

/**
 * Generate an image prompt for a monster.
 *
 * @param string $monsterName Monster name.
 * @return string
 */
function buildMonsterPrompt(string $monsterName): string {
    return "fantasy RPG monster, {$monsterName}, " .
           "dark fantasy art, dramatic lighting, highly detailed, " .
           "menacing, cinematic, digital painting";
}

/**
 * Generate an image prompt for a town scene.
 *
 * @param string $townName Town name.
 * @param string $biome    Biome type.
 * @return string
 */
function buildTownPrompt(string $townName, string $biome): string {
    return "fantasy RPG town scene, {$townName}, {$biome} setting, " .
           "medieval architecture, atmospheric, dark fantasy style, " .
           "detailed environment art, cinematic lighting";
}

/**
 * Generate an image prompt for a dungeon room.
 *
 * @param string $dungeonName Dungeon name.
 * @param string $roomType    Room type (combat, treasure, boss, etc.).
 * @return string
 */
function buildDungeonRoomPrompt(string $dungeonName, string $roomType): string {
    return "fantasy RPG dungeon room, {$dungeonName}, {$roomType} chamber, " .
           "torchlight, stone walls, dark fantasy, atmospheric, " .
           "detailed environment, cinematic composition";
}

/**
 * Generate a world map image prompt.
 *
 * @return string
 */
function buildWorldMapPrompt(): string {
    return "fantasy continent map, mountains rivers forests kingdoms roads, " .
           "aged parchment texture, dark fantasy cartography, " .
           "hand-drawn illustration style, highly detailed";
}

/**
 * Generate an item image prompt.
 *
 * @param string $itemName Item name.
 * @return string
 */
function buildItemPrompt(string $itemName): string {
    return "fantasy RPG item, {$itemName}, " .
           "detailed product illustration, dark fantasy style, " .
           "isolated on dark background, high quality digital art";
}
