# RealmForge – AI Cinematic Adventure RPG

> **Live at:** [playrealmforge.co.uk](https://playrealmforge.co.uk)

RealmForge is a browser-based dark-fantasy RPG where an AI Game Master narrates dynamic adventures while a backend game engine enforces all gameplay mechanics. Every session is unique: the world is procedurally generated, story events are AI-narrated via Groq, and scene artwork is created on demand using Stable Diffusion.

---

## Features

- **AI-Narrated Adventure** – The Groq API (llama-3.1-8b-instant) generates atmospheric 80–120 word story beats in response to player actions.
- **Procedural World** – A 50×50 tile continent with kingdoms, towns, dungeons, roads, and biomes generated on first launch.
- **Image Generation** – Stable Diffusion generates scene art, NPC portraits, monsters, items, and maps; all cached locally.
- **Player Interaction** – Choose from 3 AI-suggested actions *or* type any free-text command (max 200 chars).
- **Inventory & Combat** – Dice-based combat, loot drops, and a full item inventory system.
- **Quest System** – Multi-faction quest chains with gold and item rewards.
- **Faction Reputation** – Five factions; player actions adjust standing, unlocking discounts and quests.
- **Story Memory Compression** – Older events are summarised by the AI to prevent token overflow.
- **Admin Dashboard** – View logs, manage quests/NPCs/factions/lore, clear image cache, and regenerate the world.
- **cPanel Compatible** – Pure PHP 8+ with no Composer dependencies; runs on standard Apache shared hosting.

---

## Folder Structure

```
realmforge/
├── config.php               # API keys and global constants
├── README.md
├── PROJECT_BRIEF.md
│
├── api/                     # JSON API endpoints
│   ├── adventure.php        # Main game loop (action → AI narration)
│   ├── generateImage.php    # Image generation with caching
│   ├── npcDialogue.php      # Contextual NPC conversation
│   ├── compressMemory.php   # Story memory compression
│   ├── generateDungeon.php  # Procedural dungeon rooms
│   └── generateWorld.php    # World generation / retrieval
│
├── engine/                  # Pure PHP game engine (no HTTP)
│   ├── continentGenerator.php
│   ├── world.php
│   ├── towns.php
│   ├── dungeons.php
│   ├── inventory.php
│   ├── combat.php
│   ├── dice.php
│   ├── quests.php
│   ├── npcs.php
│   ├── shops.php
│   ├── history.php
│   ├── memory.php
│   ├── factions.php
│   ├── lore.php
│   ├── imagePrompts.php
│   ├── gameMasterPrompt.php
│   └── parser.php
│
├── public/                  # Apache document root
│   ├── index.html
│   ├── style.css
│   └── app.js
│
├── admin/                   # Password-protected admin area
│   ├── .htaccess
│   ├── dashboard.php
│   ├── logs.php
│   ├── quests.php
│   ├── npcs.php
│   ├── factions.php
│   ├── lore.php
│   ├── images.php
│   └── world.php
│
├── images/generated/        # Stable Diffusion output cache
│   ├── scenes/
│   ├── npcs/
│   ├── monsters/
│   ├── items/
│   ├── towns/
│   ├── dungeons/
│   └── maps/
│
├── logs/
│   ├── ai_requests.log
│   ├── player_actions.log
│   └── errors.log
│
└── database/
    ├── schema.sql           # Optional MySQL schema
    └── world.json           # Generated world data
```

---

## API Setup

### Groq API

1. Create an account at [console.groq.com](https://console.groq.com).
2. Generate an API key.
3. Set `GROQ_API_KEY` in `config.php` (or as a server environment variable).

### Stable Diffusion (Stability AI)

1. Create an account at [platform.stability.ai](https://platform.stability.ai).
2. Generate an API key.
3. Set `STABLE_DIFFUSION_API_KEY` in `config.php`.

> **Security:** Never expose API keys in frontend JavaScript. All API calls are server-side only.

---

## Deployment

### Requirements

- PHP 8.0+ with `curl` extension enabled
- Apache with `mod_rewrite`
- Write permissions on `images/generated/` and `logs/`

### Steps

1. **Clone or upload** the repository to your cPanel hosting account.

   ```bash
   git clone https://github.com/hostyorkshire/realmforge.git
   ```

2. **Configure API keys** in `config.php`:

   ```php
   define('GROQ_API_KEY', 'your-actual-groq-api-key');
   define('STABLE_DIFFUSION_API_KEY', 'your-actual-sd-api-key');
   ```

3. **Set write permissions** on cache directories:

   ```bash
   chmod -R 755 images/generated/
   chmod -R 755 logs/
   ```

4. **Point your domain** (`playrealmforge.co.uk`) to the `/public` directory in your cPanel document root settings.

5. **Set up admin authentication.** Edit `admin/.htaccess` to point `AuthUserFile` to a valid `.htpasswd` file, then create it:

   ```bash
   htpasswd -c /etc/realmforge/.htpasswd admin
   ```

6. **Optional – MySQL save games.** Import `database/schema.sql` into a MySQL database and add the connection credentials to `config.php`.

7. **Visit** `https://playrealmforge.co.uk` and begin your adventure!

8. On first load, the world is automatically generated and saved to `database/world.json`.

---

## Admin Dashboard

Access the admin area at `https://playrealmforge.co.uk/admin/dashboard.php`.

Protected by HTTP Basic Authentication (configure via `admin/.htaccess`).

Features:
- System overview (API key status, writable directories, log counts)
- Log viewer with clear functionality
- Quest, NPC, faction, and lore browsers
- Image cache manager
- World viewer and regeneration

---

## Configuration Reference

| Constant | Description |
|---|---|
| `GROQ_API_KEY` | Your Groq API key |
| `GROQ_ENDPOINT` | Groq completions endpoint |
| `GROQ_MODEL` | `llama-3.1-8b-instant` |
| `STABLE_DIFFUSION_API_KEY` | Your Stability AI API key |
| `STABLE_DIFFUSION_ENDPOINT` | Stability AI generation endpoint |
| `WORLD_GRID_SIZE` | World tile grid size (50×50) |
| `MAX_HISTORY_EVENTS` | Events stored before compression (5) |
| `STORY_MIN_WORDS` | Minimum narrative length (80) |
| `STORY_MAX_WORDS` | Maximum narrative length (120) |

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+, Apache |
| Frontend | HTML5, CSS3, Vanilla JS |
| AI Narration | Groq API (llama-3.1-8b-instant) |
| Image Generation | Stability AI (Stable Diffusion) |
| Database (optional) | MySQL / MariaDB |
| Hosting | cPanel shared hosting |

---

## License

MIT
