# RealmForge – AI Cinematic Adventure RPG

> **Play now:** [playrealmforge.co.uk](https://playrealmforge.co.uk)

**RealmForge** is a browser-based **dark-fantasy RPG** set in a procedurally generated world. An AI Game Master narrates every moment of your adventure—describing the sights, sounds, and dangers of each new scene in vivid prose. No two playthroughs are the same: the continent, its towns, factions, dungeons, and NPCs are all generated fresh for each installation, and every action you take shapes the story that unfolds.

Whether you are exploring haunted ruins, negotiating with a shadowy guild, or carving your way through a monster-filled dungeon, RealmForge responds to *your* choices with AI-written narration and on-demand scene artwork—making it feel like a personal tabletop campaign that never stops.

---

## How to Play / Quick Start

### 1. Your Adventure Begins

When you first visit the game, a **procedurally generated world** is created: a 50×50 tile continent filled with kingdoms, towns, biomes (forest, mountains, desert, swamp, and more), roads, and dungeons. The AI Game Master sets the scene with an atmospheric opening paragraph and drops you right into the story.

No account or download is required—just open the game in your browser and start playing.

### 2. Choose Your Next Move

At the bottom of every scene you will find **three AI-suggested actions** tailored to the current situation—attack, negotiate, explore, flee, or anything else the moment calls for. You can pick one of these with a single click, or type **any free-text command** of your own (up to 200 characters) for complete freedom.

> *"Search the body for clues."*  
> *"Offer the innkeeper a bribe."*  
> *"Charge headlong into the cave."*

### 3. The Story Unfolds

After you act, the game engine processes your choice and the **Groq AI** (llama-3.1-8b-instant) writes the next 80–120 word story beat—describing exactly what happens as a result. Simultaneously, the browser draws a unique scene illustration using **HTML5 Canvas**: landscapes, dungeon chambers, town scenes, and more—generated instantly, with no server round-trip required.

### 4. Core Gameplay Loop

| Phase | What happens |
|---|---|
| **Scene** | AI narration describes where you are and what is happening |
| **Choice** | Pick an AI-suggested action or type your own command |
| **Resolution** | The engine resolves combat (dice-based), dialogue, or exploration |
| **Art** | HTML5 Canvas draws a procedural scene illustration in the browser |
| **State update** | Your health, gold, inventory, quest progress, and reputation are updated |
| **Next scene** | The AI narrates the outcome and presents three new choices |

Your **story memory** is kept concise automatically—older events are summarised by the AI so long sessions never lose context.

### 5. World, Factions & Reputation

The world is home to **five factions**—the Kingdom of Avaros, Iron Dominion, Northern Clans, Shadow Brotherhood, and Temple of Dawn—each with their own quests, merchants, and attitudes toward you. Every action you take adjusts your reputation with the relevant faction, unlocking new quests, discounts, or hostility depending on your choices. Building alliances or burning bridges is entirely up to you.

### 6. NPCs, Dialogue & Quests

Every town and dungeon is populated with **AI-driven NPCs**. When you speak to a character, the AI generates contextually appropriate dialogue based on who they are, where you are, and your current reputation. NPCs offer **multi-step quests** with gold and item rewards. Complete them to advance faction standing and unlock new areas and storylines.

### 7. Inventory & Combat

Weapons, armour, potions, and loot drops are tracked in a persistent **inventory system**. Combat is resolved by the engine using a dice-based system—your equipped gear influences the outcome. Defeated enemies drop items; merchants buy and sell; dungeons hide rare loot behind locked doors and boss encounters.

### 8. Admin Dashboard (Game Masters & Administrators)

If you are hosting your own RealmForge installation, the **admin dashboard** at `/admin/dashboard.php` gives you full control:

- Monitor AI logs and player action history
- Browse and edit quests, NPCs, factions, and world lore
- View image-generation info (canvas-based, no cache to manage)
- View or regenerate the entire world

The dashboard is password-protected and intended for the host/GM, not players. See the [Deployment](#deployment-cpanel-shared-hosting) section for setup details.

---

## Features

- **AI-Narrated Adventure** – The Groq API (llama-3.1-8b-instant) generates atmospheric 80–120 word story beats in response to player actions.
- **Procedural World** – A 50×50 tile continent with kingdoms, towns, dungeons, roads, and biomes generated on first launch.
- **Canvas Scene Illustrations** – Each story beat instantly renders a procedural scene illustration in the browser using HTML5 Canvas; no server-side image API or cache required.
- **Player Interaction** – Choose from 3 AI-suggested actions *or* type any free-text command (max 200 chars).
- **Inventory & Combat** – Dice-based combat, loot drops, and a full item inventory system.
- **Quest System** – Multi-faction quest chains with gold and item rewards.
- **Faction Reputation** – Five factions; player actions adjust standing, unlocking discounts and quests.
- **Story Memory Compression** – Older events are summarised by the AI to prevent token overflow.
- **Admin Dashboard** – View logs, manage quests/NPCs/factions/lore, and regenerate the world.
- **cPanel Compatible** – Pure PHP 8+ with no Composer dependencies; runs on standard Apache shared hosting.

---

## Repository & On-Server Directory Layout

The **repository structure mirrors the server path structure exactly**. The repo
is cloned into `/home/playrealm/` (the cPanel home directory), so every folder
you see in the repo maps directly to the same path on the server.

The `public_html/` subfolder inside the repo becomes the Apache web root
(`/home/playrealm/public_html/`). Runtime write-directories (`logs/`,
`database/`) sit **above** `public_html/` in the home directory, so they are
never reachable via HTTP. The `public_html/.htaccess` blocks direct access to
every sensitive path inside the web root.

```
/home/playrealm/               ← cPanel home directory / git repository root
│
├── .cpanel.yml                # cPanel Git deployment tasks
├── .github/workflows/
│   └── deploy.yml             # GitHub Actions → cPanel auto-deploy
├── README.md
├── config.php                 # API keys & paths – above the web root, never HTTP-accessible
│
├── engine/                    # PHP game engine – above the web root, never HTTP-accessible
│   └── *.php
│
├── database/                  # Schema + generated world data – above the web root
│   ├── schema.sql
│   └── world.json             # Generated on first launch
│
└── public_html/               ← Apache web root (https://playrealmforge.co.uk)
    ├── .htaccess              # Security rules – blocks dotfiles and documentation
    │
    ├── api/                   # JSON API endpoints (web-accessible PHP)
    │   ├── adventure.php      # Main game loop (action → AI narration)
    │   ├── generateImage.php  # Canvas stub (returns {canvas:true}; images drawn in browser)
    │   ├── npcDialogue.php    # Contextual NPC conversation
    │   ├── compressMemory.php # Story memory compression
    │   ├── generateDungeon.php# Procedural dungeon rooms
    │   └── generateWorld.php  # World generation / retrieval
    │
    ├── public/                # Game frontend – served at /public/
    │   ├── index.html
    │   ├── style.css
    │   └── app.js
    │
    ├── admin/                 # Password-protected admin area
    │   ├── .htaccess
    │   └── *.php
    │
    └── images/                # Static assets only (no generated image cache)

NOTE: The logs/ directory is created automatically by .cpanel.yml on
first deploy. It lives above public_html and is never web-accessible:

  /home/playrealm/logs/          ← ai_requests.log, player_actions.log, errors.log
```

---

## API Setup

### Groq API

1. Create an account at [console.groq.com](https://console.groq.com).
2. Generate an API key.
3. Set `GROQ_API_KEY` in `config.php` (or as a server environment variable).

> **Security:** Never expose API keys in frontend JavaScript. All API calls are server-side only.

> **Note:** No image-generation API key is required. Scene illustrations are rendered
> entirely in the browser via HTML5 Canvas.

---

## Deployment (cPanel Shared Hosting)

> These instructions assume the cPanel account username **`playrealm`** with the
> default document root `/home/playrealm/public_html/` and Apache + PHP 8.0+.
> No Composer, Node.js, or SSH root access is required.

### Prerequisites

| Requirement | Where to check in cPanel |
|---|---|
| **PHP 8.0+** with the `curl` extension | *MultiPHP Manager* or *Select PHP Version* |
| **Apache** with `mod_rewrite` enabled | Enabled by default on most hosts |
| A registered **domain or subdomain** | *Domains* → *Subdomains* / *Addon Domains* |
| **(Optional) MySQL / MariaDB** database | *MySQL® Databases* |

> **Tip:** If your host offers both PHP 8.x versions, pick the latest stable
> release (e.g. 8.2). RealmForge has no Composer dependencies, so upgrades are
> painless.

---

### Step 1 – Upload the Files

> **Important:** The repository mirrors the server path structure. The repo root
> corresponds to `/home/playrealm/` (your cPanel home), and the `public_html/`
> subfolder inside the repo becomes `/home/playrealm/public_html/` (the web
> root). Upload files accordingly.

Choose **one** of the three methods below.

#### Option A – cPanel File Manager (no SSH needed)

1. Download the repository as a ZIP from GitHub:
   `https://github.com/hostyorkshire/realmforge/archive/refs/heads/main.zip`
2. In cPanel, open **File Manager** → navigate to `/home/playrealm/` (your home directory, **not** inside `public_html/`).
3. Click **Upload** → select the ZIP file → wait for the upload to finish.
4. Select the uploaded ZIP → click **Extract** → extract into `/home/playrealm/`.
5. If the ZIP extracts into a subdirectory (e.g. `realmforge-main/`), move the
   contents (`.cpanel.yml`, `.github/`, `public_html/`, etc.) one level up so
   they sit directly inside `/home/playrealm/`.
6. The `public_html/` folder from the ZIP will merge with the existing
   `/home/playrealm/public_html/` web root, placing `config.php`, `admin/`,
   `api/`, etc. correctly inside the web root.

#### Option B – cPanel Terminal / SSH

If your host enables **Terminal** (cPanel → *Advanced* → *Terminal*):

```bash
# Clone into the home directory (the . means "current directory, not a subfolder")
cd ~
git clone https://github.com/hostyorkshire/realmforge.git .
```

#### Option C – FTP / SFTP

Upload the repository contents with an FTP client such as FileZilla:

- **Host:** your server hostname (see cPanel → *FTP Accounts*)
- **Remote path:** `/home/playrealm/` (your home directory)

> **Tip:** Whichever method you use, the final result should have `config.php`,
> `public/`, `api/`, `admin/`, etc. directly inside
> `/home/playrealm/public_html/`, and `.cpanel.yml` directly inside
> `/home/playrealm/`.

---

### Step 2 – Select the Correct PHP Version

1. In cPanel, open **MultiPHP Manager** (or **Select PHP Version** on CloudLinux hosts).
2. Locate your domain in the list and set the PHP version to **8.0** or higher.
3. If your host offers a **PHP Extensions** page, confirm that the **curl** extension is enabled.

> **Why curl?** RealmForge calls the Groq API from the server
> side using PHP's `curl_*` functions.

---

### Step 3 – Document Root and Security (.htaccess)

The `public_html/` subfolder inside the repository maps to
`/home/playrealm/public_html/`, which **is** the cPanel default document root.
No domain reconfiguration is needed.

**Sensitive files live above the web root.** `config.php` (API keys), `engine/`
(game logic), and `database/` (schema and world data) are stored at
`/home/playrealm/` — outside `public_html/` entirely — so Apache will never
serve them regardless of `.htaccess` configuration.

The included `.htaccess` file at `public_html/.htaccess` provides additional
defence in depth:

1. **Directory listing** – disabled via `Options -Indexes`.

2. **Dotfiles & documentation** – blocks direct HTTP access to deployment
   internals and documentation that may exist inside the web root:

   | Blocked pattern | Examples |
   |---|---|
   | Dotfiles and dot-directories | `.git/`, `.github/`, `.htaccess` sub-files |
   | `*.md` files | Any Markdown documentation |

3. **Root redirect** – a request to `https://playrealmforge.co.uk/` is
   automatically sent to `https://playrealmforge.co.uk/public/` where the game
   frontend lives.

> **Note:** `mod_rewrite` must be enabled on your host (it is on virtually all
> cPanel / Apache servers). If the redirect does not work, confirm it is enabled
> in *MultiPHP Manager* or contact your host.

---

### Step 4 – Configure API Keys

1. In **File Manager**, navigate to `/home/playrealm/` (your home directory, **not**
   `public_html/`) and open `config.php` for editing.
2. Replace the placeholder value with your real key:

   ```php
   define('GROQ_API_KEY', 'gsk_YourActualGroqKeyHere');
   ```

3. While you're in the file, change the default admin credentials:

   ```php
   define('ADMIN_USER', 'myadmin');
   define('ADMIN_PASS', 'a-strong-random-password');
   ```

4. For production, confirm these two lines are set (they are by default):

   ```php
   ini_set('display_errors', 0);   // hide errors from visitors
   ini_set('log_errors', 1);       // write errors to /home/playrealm/logs/errors.log
   ```

5. **Save** the file.

> **Security:** `config.php` sits above `public_html/` and is therefore never
> accessible via HTTP. It is included by PHP scripts using relative paths.

---

### Step 5 – Set Directory Permissions

RealmForge writes log files and world data at runtime. These are stored
**above** `public_html/` so they are never reachable via HTTP.

**Via Terminal:**

```bash
chmod -R 755 ~/logs/
chmod -R 755 ~/database/
```

> The `logs/` and `database/` directories are created automatically by
> `.cpanel.yml` on the first deploy. If you uploaded files manually, create
> them now:
>
> ```bash
> mkdir -p ~/logs ~/database
> chmod 755 ~/logs ~/database
> ```

---

### Step 6 – Protect the Admin Dashboard

The admin area at `/admin/dashboard.php` is protected by HTTP Basic Auth via
`admin/.htaccess`. You need to create a `.htpasswd` file above the web root.

**Via cPanel Terminal:**

```bash
# Create the password file above public_html (not web-accessible)
mkdir -p ~/.htpassfiles
htpasswd -c ~/.htpassfiles/.htpasswd admin

# To add more users later, omit the -c flag (it overwrites the file):
# htpasswd ~/.htpassfiles/.htpasswd anotheruser
```

The `admin/.htaccess` file already points to this location:

```apache
AuthUserFile /home/playrealm/.htpassfiles/.htpasswd
```

**Via cPanel's "Directory Privacy" tool:**

1. In cPanel, go to **Directory Privacy** (under *Security*).
2. Navigate to `public_html/admin/`.
3. Check **Password protect this directory**, give it a label, and save.
4. Add a user with a strong password.

> **Tip:** The "Directory Privacy" method automatically generates the
> `.htaccess` and `.htpasswd` entries for you—no command line needed. If you
> use this method, remove the existing `admin/.htaccess` first so cPanel can
> write its own.

---

### Step 7 – (Optional) Set Up MySQL for Save Games

RealmForge works entirely with JSON files by default. If you want persistent
database-backed save games:

1. In cPanel, open **MySQL® Databases**.
2. Create a new database (e.g. `playrealm_realmforge`).
3. Create a new database user and assign it **ALL PRIVILEGES** on that database.
4. Open **phpMyAdmin** (cPanel → *Databases* → *phpMyAdmin*).
5. Select your new database in the left sidebar.
6. Click the **Import** tab → choose `database/schema.sql` from your local machine → click **Go**.
7. Add the connection details to `config.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'playrealm_realmforge');
   define('DB_USER', 'playrealm_rfuser');
   define('DB_PASS', 'your-db-password');
   ```

> **Tip:** On most cPanel hosts the database name and user are prefixed with
> your cPanel username (e.g. `playrealm_realmforge`). Use the exact names shown in
> the MySQL Databases screen.

---

### Step 8 – Verify the Installation

1. **Visit your domain** (e.g. `https://playrealmforge.co.uk`).
   - You should see the RealmForge game interface with the "Your adventure begins…" placeholder.
2. **Click an action** or type a custom command. If Groq is configured correctly you will receive an AI-narrated response within a few seconds.
3. **Check scene illustrations.** A procedural canvas illustration should appear above the story text instantly after each action. If it does not appear, open the browser console (F12) and check for JavaScript errors.
4. On **first load** the world is automatically generated and saved to `database/world.json`. This may take a moment.
5. **Visit the admin dashboard** at `https://playrealmforge.co.uk/admin/dashboard.php` and log in with the credentials you configured.

---

## Automatic Deployment (cPanel Git + GitHub Actions)

> Every time you merge a pull request on GitHub the site code **and** database
> schema are automatically updated on your cPanel development server. This uses
> cPanel's built-in **Git Version Control** feature together with a small GitHub
> Actions workflow.

### How It Works

1. A PR is merged into the `main` branch on GitHub.
2. The **Deploy to cPanel** GitHub Actions workflow fires and calls the cPanel
   API to pull the latest code.
3. cPanel pulls from GitHub and reads the `.cpanel.yml` file in the repository
   root.
4. `.cpanel.yml` runs the deployment tasks: creates runtime directories, sets
   permissions, and (optionally) imports the database schema.

### Step 1 – Create a Git Repository in cPanel

1. Log in to cPanel and go to **Git™ Version Control** (under *Files*).
2. Click **Create**.
3. Toggle **Clone a Repository** on.
4. Fill in the fields:

   | Field | Value |
   |---|---|
   | **Clone URL** | `https://github.com/hostyorkshire/realmforge.git` |
   | **Repository Path** | `/home/playrealm` |
   | **Repository Name** | `realmforge` |

   > **Why `/home/playrealm` and not `/home/playrealm/public_html`?**
   > The repository structure mirrors the server path structure. The repo root
   > maps to your cPanel home directory, and the `public_html/` subfolder in
   > the repo maps to `/home/playrealm/public_html/` (the web root). This makes
   > it immediately clear in the repo which files are web-accessible and which
   > are not.

5. Click **Create**. cPanel will clone the repo into your home directory and run
   the `.cpanel.yml` deployment tasks automatically on the first pull.

### Step 2 – Generate a cPanel API Token

The GitHub Actions workflow needs an API token to tell cPanel to pull new
changes.

1. In cPanel, go to **Manage API Tokens** (under *Security*).
2. Click **Create** and give the token a name (e.g. `github-deploy`).
3. Copy the generated token — you will need it in the next step.

### Step 3 – Add GitHub Repository Secrets

In your GitHub repository, go to **Settings → Secrets and variables → Actions**
and create the following **Repository secrets**:

| Secret | Example value | Description |
|---|---|---|
| `CPANEL_USERNAME` | `playrealm` | Your cPanel login username |
| `CPANEL_API_TOKEN` | `WBLY3E0JKH…` | The API token from Step 2 |
| `CPANEL_HOST` | `server.hostyorkshire.co.uk` | cPanel server hostname |
| `CPANEL_REPO_PATH` | `/home/playrealm` | Full path to the repository root on the server |

### Step 4 – (Optional) Enable Automatic Database Updates

The `.cpanel.yml` file includes a commented-out task that imports
`database/schema.sql` into MySQL on every deploy. The SQL uses
`CREATE TABLE IF NOT EXISTS`, so it is safe to run repeatedly — it will create
missing tables without affecting existing data.

To enable it:

1. Open `.cpanel.yml` in the repository root.
2. Uncomment the last line and replace the placeholders with your cPanel MySQL
   credentials:

   ```yaml
   - /usr/bin/mysql -u playrealm_rfuser -p"your-db-password" playrealm_realmforge < /home/playrealm/public_html/database/schema.sql
   ```

3. Commit and push the change; the next deploy will run the import
   automatically.

> **Security:** Avoid committing plain-text database passwords. A safer
> alternative is to store credentials in a non-committed file on the server
> (e.g. `~/.my.cnf`) so `mysql` authenticates automatically, or to read them
> from environment variables in a small deploy script. If your repository is
> public, **never** put credentials in `.cpanel.yml`.

### Step 5 – Test the Pipeline

1. Create a branch, make a small change, and open a pull request.
2. Merge the PR into `main`.
3. Go to **Actions** in your GitHub repository — you should see the *Deploy to
   cPanel* workflow run and succeed.
4. In cPanel → **Git™ Version Control**, click **Manage** next to `realmforge`
   and confirm it shows the latest commit.
5. Visit your site to verify the change is live.

### Deployment File Reference

| File | Purpose |
|---|---|
| `.cpanel.yml` | Defines post-pull deployment tasks (directory creation, permissions, optional DB import) |
| `.github/workflows/deploy.yml` | GitHub Actions workflow that triggers the cPanel pull via the UAPI on every push to `main` |

---

### Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| **500 Internal Server Error** | PHP version too low or `mod_rewrite` disabled | Set PHP to 8.0+ in *MultiPHP Manager*; check `.htaccess` syntax |
| **Blank page / white screen** | PHP fatal error hidden by `display_errors 0` | Check `/home/playrealm/logs/errors.log` or temporarily set `display_errors` to `1` in `config.php` |
| **"Failed to connect to api.groq.com"** | `curl` extension not enabled | Enable `curl` in *Select PHP Version* → *Extensions* |
| **Images not appearing** | Browser canvas rendering error | Open the browser console (F12) for any JavaScript errors |
| **Admin page shows "401 Unauthorized"** | `.htpasswd` path wrong in `admin/.htaccess` | Run `realpath ~/.htpassfiles/.htpasswd` in Terminal to get the correct absolute path |
| **World not generating** | `/home/playrealm/database/` not writable or missing | `mkdir -p ~/database && chmod 755 ~/database` |
| **API rate-limit errors** | Too many requests to Groq | Wait a minute and try again; check your plan's rate limits |

### Security Tips

- **Sensitive files live above the web root.** `config.php` (API keys),
  `engine/` (game logic), and `database/` (world data) are stored at
  `/home/playrealm/` — outside `public_html/` entirely — so they can never be
  reached via HTTP.
- **`.htaccess` provides defence in depth.** Dotfiles (`.git/`, `.github/`) and
  documentation (`*.md`) inside the web root are blocked by `.htaccess`. Never
  delete or weaken these rules.
- **Use HTTPS.** Enable a free SSL certificate via cPanel → *SSL/TLS Status*
  or *Let's Encrypt™* and force HTTPS in `.htaccess`.
- **Restrict `admin/` access** to your own IP address if possible by adding
  `Require ip <your-ip>` to `admin/.htaccess`.
- **Change the default admin password** in `config.php` before going live.
- **Disable directory listing** – already enforced by `Options -Indexes` in the
  root `.htaccess`.

---

## Admin Dashboard

Access the admin area at `https://playrealmforge.co.uk/admin/dashboard.php`.

Protected by HTTP Basic Authentication (configure via `admin/.htaccess`).

Features:
- System overview (API key status, log counts)
- Log viewer with clear functionality
- Quest, NPC, faction, and lore browsers
- Image generation info (HTML5 Canvas, no cache to manage)
- World viewer and regeneration

---

## Configuration Reference

| Constant | Description |
|---|---|
| `GROQ_API_KEY` | Your Groq API key |
| `GROQ_ENDPOINT` | Groq completions endpoint |
| `GROQ_MODEL` | `llama-3.1-8b-instant` |
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
| Image Generation | HTML5 Canvas (browser-side, procedural) |
| Database (optional) | MySQL / MariaDB |
| Hosting | cPanel shared hosting |

---

## License

MIT
