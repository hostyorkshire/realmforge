# RealmForge – Project Brief

## Project Name
RealmForge – AI Cinematic Adventure RPG

## Production Domain
https://playrealmforge.co.uk

## Overview
RealmForge is a browser-based fantasy RPG where an AI Game Master narrates dynamic adventures while a backend game engine enforces gameplay mechanics. Players explore a procedurally generated world containing towns, NPC characters, monsters, factions, quests, items, procedural dungeons, kingdoms, roads, and wilderness. Story narration is generated using the Groq API. Images are generated using Stable Diffusion and cached locally.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+, Apache (cPanel) |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| AI Narration | Groq API – llama-3.1-8b-instant |
| Image Generation | Stable Diffusion API |
| Database (optional) | MySQL |

## Core Gameplay Loop
1. Player sees a scene image and AI-narrated story (80–120 words)
2. Player chooses one of 3 suggested actions OR types a custom command
3. Backend processes the action (combat, exploration, dialogue, etc.)
4. Groq generates the next story beat and 3 new choices
5. Stable Diffusion generates a scene image (cached by prompt hash)
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

## Image Caching
- Before generation: hash the prompt with MD5
- If `images/generated/{type}/{hash}.png` exists: return it
- Otherwise: call Stable Diffusion API, save result, return URL

## Security
- API keys stored only in `config.php` (server-side)
- Admin area protected by HTTP Basic Auth via `.htaccess`
- All player input sanitised and stripped of HTML/injection attempts
- Input capped at 200 characters

## Deployment
1. Clone repo to cPanel hosting
2. Configure `config.php` with real API keys
3. Ensure `images/generated/` and `logs/` are writable
4. Point `playrealmforge.co.uk` document root to `/public`
5. Configure admin `.htpasswd` authentication
6. Visit site – world auto-generates on first load
