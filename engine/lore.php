<?php
/**
 * Lore System
 * Stores world lore, legends, and background narrative.
 */

const WORLD_LORE = [
    'creation' => "In the age before memory, the gods shaped the continent of Aeloria from primordial chaos. " .
                  "They raised mountains, filled seas, and breathed life into the land. But the gods quarrelled, " .
                  "and their war shattered the ancient realm, leaving mortals to rebuild from the ruins.",

    'the_sundering' => "Five hundred years ago, a great cataclysm known as the Sundering tore the eastern kingdoms " .
                       "apart. Dark magic from the Void ripped through the land, creating the Blighted Wastes and " .
                       "unleashing monsters into the world. Only the Temple of Dawn's intervention sealed the rift.",

    'kingdom_avaros_history' => "The Kingdom of Avaros was founded three centuries ago by King Aldric the First, " .
                                "a knight of legendary virtue. His descendants have ruled wisely, though recent years " .
                                "have seen growing tension with the expanding Iron Dominion.",

    'shadow_brotherhood_origins' => "The Shadow Brotherhood began as a guild of spies serving the old empire. " .
                                    "When the empire fell, they turned to crime and intrigue. They are rumoured to " .
                                    "have agents in every city and court in Aeloria.",

    'the_relic_of_ages' => "The Relic of Ages is an artefact of immense power, said to be a fragment of the " .
                           "gods' original creation. Scholars believe it was buried in the Crypt of Shadows " .
                           "to keep it from being misused. Both the Temple of Dawn and the Shadow Brotherhood " .
                           "seek it for their own purposes.",
];

/**
 * Get a specific lore entry.
 *
 * @param string $key Lore entry key.
 * @return string|null Lore text or null if not found.
 */
function getLore(string $key): ?string {
    return WORLD_LORE[$key] ?? null;
}

/**
 * Get all lore entries.
 *
 * @return array
 */
function getAllLore(): array {
    return WORLD_LORE;
}

/**
 * Build a compact lore summary for the AI Game Master prompt.
 *
 * @return string
 */
function getLoreSummary(): string {
    return "The continent of Aeloria is home to the Kingdom of Avaros (benevolent monarchy), " .
           "the Iron Dominion (militaristic empire), the Northern Clans (fierce warriors), " .
           "the Shadow Brotherhood (criminal guild), and the Temple of Dawn (religious order). " .
           "A great cataclysm called the Sundering shaped the current age. " .
           "The Relic of Ages is a coveted artefact hidden in the Crypt of Shadows.";
}
