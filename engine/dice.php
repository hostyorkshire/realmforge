<?php
/**
 * Dice System
 * Provides random dice roll mechanics for gameplay events.
 */

/**
 * Roll a single die with the given number of sides.
 *
 * @param int $sides Number of sides on the die (e.g. 6, 20).
 * @return int Result between 1 and $sides inclusive.
 */
function rollDice(int $sides): int {
    return random_int(1, max(1, $sides));
}

/**
 * Roll multiple dice and return the total.
 *
 * @param int $count Number of dice to roll.
 * @param int $sides Number of sides per die.
 * @return int Sum of all rolls.
 */
function rollMultiple(int $count, int $sides): int {
    $total = 0;
    for ($i = 0; $i < $count; $i++) {
        $total += rollDice($sides);
    }
    return $total;
}

/**
 * Perform a skill check against a difficulty threshold.
 *
 * @param int $difficulty Minimum roll required to succeed (default 8).
 * @return array ['success' => bool, 'roll' => int]
 */
function skillCheck(int $difficulty = 8): array {
    $roll = rollDice(20);
    return [
        'success' => $roll >= $difficulty,
        'roll'    => $roll,
    ];
}
