<?php
/**
 * Shop System
 */

const SHOP_ITEMS = [
    ['name' => 'Sword',          'price' => 30, 'type' => 'weapon'],
    ['name' => 'Shield',         'price' => 25, 'type' => 'armor'],
    ['name' => 'Torch',          'price' => 5,  'type' => 'tool'],
    ['name' => 'Rope',           'price' => 8,  'type' => 'tool'],
    ['name' => 'Health Potion',  'price' => 15, 'type' => 'consumable'],
    ['name' => 'Mana Crystal',   'price' => 20, 'type' => 'consumable'],
    ['name' => 'Iron Dagger',    'price' => 18, 'type' => 'weapon'],
    ['name' => 'Leather Armour', 'price' => 35, 'type' => 'armor'],
    ['name' => 'Map Fragment',   'price' => 10, 'type' => 'tool'],
    ['name' => 'Spell Scroll',   'price' => 40, 'type' => 'magic'],
];

/**
 * Return available shop items, optionally filtered by type.
 *
 * @param string|null $type Item type filter.
 * @return array
 */
function getShopItems(?string $type = null): array {
    if ($type === null) {
        return SHOP_ITEMS;
    }
    return array_values(array_filter(SHOP_ITEMS, fn($item) => $item['type'] === $type));
}

/**
 * Attempt to purchase an item.
 *
 * @param array  $state    Player state.
 * @param string $itemName Name of item to purchase.
 * @return array ['state' => updated state, 'success' => bool, 'message' => string]
 */
function purchaseItem(array $state, string $itemName): array {
    require_once __DIR__ . '/inventory.php';

    foreach (SHOP_ITEMS as $item) {
        if (strtolower($item['name']) === strtolower($itemName)) {
            if (($state['gold'] ?? 0) < $item['price']) {
                return [
                    'state'   => $state,
                    'success' => false,
                    'message' => "You cannot afford {$item['name']}. It costs {$item['price']} gold.",
                ];
            }
            $state['gold'] -= $item['price'];
            $state = addItem($state, $item['name']);
            return [
                'state'   => $state,
                'success' => true,
                'message' => "You purchased {$item['name']} for {$item['price']} gold.",
            ];
        }
    }

    return [
        'state'   => $state,
        'success' => false,
        'message' => "Item '{$itemName}' not found in shop.",
    ];
}
