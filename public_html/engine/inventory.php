<?php
/**
 * Inventory System
 */

/**
 * Add an item to the player's inventory.
 *
 * @param array  $state Player state array.
 * @param string $item  Item name to add.
 * @return array Updated player state.
 */
function addItem(array $state, string $item): array {
    if (!isset($state['inventory'])) {
        $state['inventory'] = [];
    }
    $state['inventory'][] = $item;
    return $state;
}

/**
 * Remove the first occurrence of an item from the player's inventory.
 *
 * @param array  $state Player state array.
 * @param string $item  Item name to remove.
 * @return array Updated player state.
 */
function removeItem(array $state, string $item): array {
    $inventory = $state['inventory'] ?? [];
    $key = array_search($item, $inventory);
    if ($key !== false) {
        array_splice($inventory, $key, 1);
    }
    $state['inventory'] = $inventory;
    return $state;
}

/**
 * Check whether the player has a specific item.
 *
 * @param array  $state Player state array.
 * @param string $item  Item name to check.
 * @return bool
 */
function hasItem(array $state, string $item): bool {
    return in_array($item, $state['inventory'] ?? [], true);
}
