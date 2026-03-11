<?php
/**
 * Combat System
 */

require_once __DIR__ . '/dice.php';
require_once __DIR__ . '/inventory.php';

// Default monster definitions used when no specific monster is provided
const DEFAULT_MONSTERS = [
    ['name' => 'Goblin',          'health' => 20, 'damage' => [1, 6],  'loot' => ['Gold Coin', 'Rusty Dagger']],
    ['name' => 'Skeleton Warrior','health' => 30, 'damage' => [1, 8],  'loot' => ['Bone', 'Iron Sword']],
    ['name' => 'Forest Troll',    'health' => 50, 'damage' => [2, 6],  'loot' => ['Club', 'Health Potion']],
    ['name' => 'Dark Mage',       'health' => 25, 'damage' => [1, 10], 'loot' => ['Spell Scroll', 'Mana Crystal']],
    ['name' => 'Bandit',          'health' => 30, 'damage' => [1, 8],  'loot' => ['Gold Coin', 'Short Sword']],
];

/**
 * Simulate a combat round against a monster.
 *
 * @param array      $state   Player state array.
 * @param array|null $monster Optional monster definition array.
 * @return array ['state' => updated state, 'result' => narrative string, 'victory' => bool]
 */
function attackMonster(array $state, ?array $monster = null): array {
    if ($monster === null) {
        $monster = DEFAULT_MONSTERS[array_rand(DEFAULT_MONSTERS)];
    }

    $enemyHealth  = $monster['health'];
    $narrative    = [];
    $victory      = false;
    $rounds       = 0;
    $maxRounds    = 20;

    while ($enemyHealth > 0 && ($state['health'] ?? 100) > 0 && $rounds < $maxRounds) {
        $rounds++;

        // Player attacks
        [$diceCount, $diceSides] = [1, 8];
        $playerDamage = rollMultiple($diceCount, $diceSides);
        $enemyHealth -= $playerDamage;
        $narrative[] = "You strike the {$monster['name']} for {$playerDamage} damage.";

        if ($enemyHealth <= 0) {
            $victory = true;
            break;
        }

        // Enemy attacks
        [$eDiceCount, $eDiceSides] = $monster['damage'];
        $enemyDamage = rollMultiple($eDiceCount, $eDiceSides);
        $state['health'] = max(0, ($state['health'] ?? 100) - $enemyDamage);
        $narrative[] = "The {$monster['name']} hits you for {$enemyDamage} damage. You have {$state['health']} HP remaining.";

        if ($state['health'] <= 0) {
            break;
        }
    }

    if ($victory) {
        $narrative[] = "You defeated the {$monster['name']}!";
        // Award loot
        $lootPool = $monster['loot'] ?? [];
        if (!empty($lootPool)) {
            $loot = $lootPool[array_rand($lootPool)];
            $state  = addItem($state, $loot);
            $narrative[] = "You found: {$loot}.";
        }
        // Award gold
        $gold = rollDice(10) + 5;
        $state['gold'] = ($state['gold'] ?? 0) + $gold;
        $narrative[] = "You gained {$gold} gold.";
    } elseif (($state['health'] ?? 0) <= 0) {
        $narrative[] = "You have been defeated! You wake up at the nearest town.";
        $state['health'] = 50; // Respawn with partial health
    }

    return [
        'state'   => $state,
        'result'  => implode(' ', $narrative),
        'victory' => $victory,
    ];
}
