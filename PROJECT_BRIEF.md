# RealmForge – Project Brief

## Project Name
RealmForge – AI Cinematic Adventure RPG

## Production Domain
https://playrealmforge.co.uk

## Overview
RealmForge is a browser-based fantasy RPG where an AI Game Master narrates dynamic adventures while a backend game engine enforces gameplay mechanics. Players explore a procedurally generated world containing towns, NPC characters, monsters, factions, quests, items, procedural dungeons, kingdoms, roads, and wilderness. Story narration is generated using the Groq API. Scene illustrations are generated in the browser using HTML5 Canvas — no server-side image API is required.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+, Apache (cPanel) |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| AI Narration | Groq API – llama-3.1-8b-instant |
| Image Generation | HTML5 Canvas (browser-side, procedural) |
| Database (optional) | MySQL |

## Core Gameplay Loop
1. Player sees a canvas-rendered scene illustration and AI-narrated story (80–120 words)
2. Player chooses one of 3 suggested actions OR types a custom command
3. Backend processes the action (combat, exploration, dialogue, etc.)
4. Groq generates the next story beat and 3 new choices
5. Browser draws a new procedural scene illustration on `<canvas>` using the current location context
6. Player state (location, health, gold, inventory, quests, reputation) persists

## World Structure
- **Grid:** 50×50 tiles
- **Biomes:** forest, plains, mountains, desert, swamp, coast
- **Kingdoms:** Kingdom of Avaros, Iron Dominion, Northern Clans
- **Factions:** Kingdom of Avaros, Iron Dominion, Northern Clans, Shadow Brotherhood, Temple of Dawn
- **Towns:** Stonebridge Village, Ravenmoor Town, Ironpeak Settlement, Dawn Harbour
- **Dungeons:** Crypt of Shadows, Goblin Cave, Forgotten Catacombs, Dragon's Lair

## AI Rules
The AI Game Master:
- Narrates events in vivid second-person prose (80–120 words)
- Returns exactly 3 suggested actions (prefixed `CHOICE:`)
- Must NOT control the player, change inventory, or invent map locations

## Memory System
- Only the last 5 events are stored in `history[]`
- Older events are summarised via Groq and stored in `story_summary`
- Prevents AI token overflow on long sessions

## Image Generation
Scene illustrations are generated entirely in the browser using HTML5 Canvas.
The JavaScript function `drawSceneCanvas()` in `public/app.js` renders a
procedural scene (wilderness, town, or dungeon) based on the player's current
location and last action. No server-side image generation, API keys, or caching
are involved.

## Security
- API keys stored only in `config.php` (server-side)
- Admin area protected by HTTP Basic Auth via `.htaccess`
- All player input sanitised and stripped of HTML/injection attempts
- Input capped at 200 characters

## Deployment
1. Clone repo into `/home/playrealm` (the cPanel home directory – the repo structure mirrors the server paths, with `public_html/` as a subfolder)
2. Configure `config.php` with the Groq API key
3. `.cpanel.yml` creates `~/logs/` and `~/database/` above the web root on first deploy; ensure they are writable
4. `config.php`, `engine/`, and `database/` sit above the web root and are never HTTP-accessible. `public_html/.htaccess` blocks dotfiles and docs. `public_html/index.php` redirects `/` to `/public/`
5. Configure admin `.htpasswd` at `~/.htpassfiles/.htpasswd`
6. Visit site – world auto-generates on first load
