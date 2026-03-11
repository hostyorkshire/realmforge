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
├── .cpanel.yml              # cPanel Git deployment tasks
├── .github/workflows/
│   └── deploy.yml           # GitHub Actions → cPanel auto-deploy
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

## Deployment (cPanel Shared Hosting)

> These instructions assume a standard cPanel shared-hosting account with Apache
> and PHP 8.0+. No Composer, Node.js, or SSH root access is required.

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

Choose **one** of the three methods below.

#### Option A – cPanel File Manager (no SSH needed)

1. Download the repository as a ZIP from GitHub:
   `https://github.com/hostyorkshire/realmforge/archive/refs/heads/main.zip`
2. In cPanel, open **File Manager** → navigate to your home directory (usually `/home/<user>/`).
3. Click **Upload** → select the ZIP file → wait for the upload to finish.
4. Select the uploaded ZIP → click **Extract** → extract into your home directory.
5. Rename the extracted folder (e.g. `realmforge-main`) to `realmforge`.

#### Option B – cPanel Terminal / SSH

If your host enables **Terminal** (cPanel → *Advanced* → *Terminal*):

```bash
cd ~
git clone https://github.com/hostyorkshire/realmforge.git
```

#### Option C – FTP / SFTP

Upload the repository contents with an FTP client such as FileZilla:

- **Host:** your server hostname (see cPanel → *FTP Accounts*)
- **Remote path:** `/home/<user>/realmforge/`

> **Tip:** Whichever method you use, the final path should look like
> `/home/<user>/realmforge/` with `config.php`, `public/`, `api/`, etc. directly
> inside it.

---

### Step 2 – Select the Correct PHP Version

1. In cPanel, open **MultiPHP Manager** (or **Select PHP Version** on CloudLinux hosts).
2. Locate your domain in the list and set the PHP version to **8.0** or higher.
3. If your host offers a **PHP Extensions** page, confirm that the **curl** extension is enabled.

> **Why curl?** RealmForge calls the Groq and Stability AI APIs from the server
> side using PHP's `curl_*` functions.

---

### Step 3 – Point Your Domain to the `/public` Directory

The game's front-end files (`index.html`, `style.css`, `app.js`) live in
`realmforge/public/`. Your domain's document root must point there so that
`config.php`, `engine/`, and `api/` stay **outside** the web root for security.

1. In cPanel, go to **Domains** (or **Addon Domains** / **Subdomains**).
2. Edit the domain you want to use (e.g. `playrealmforge.co.uk`).
3. Set **Document Root** to:

   ```
   /home/<user>/realmforge/public
   ```

4. Save the change.

> **Tip:** If you cannot change the document root (some hosts lock it to
> `public_html`), you can create a symbolic link instead:
>
> ```bash
> # In cPanel Terminal – remove default public_html content first
> rm -rf /home/<user>/public_html
> ln -s /home/<user>/realmforge/public /home/<user>/public_html
> ```
>
> Or move the contents of `public/` into `public_html/` and adjust paths in
> `config.php` accordingly.

---

### Step 4 – Configure API Keys

1. In **File Manager**, navigate to `/home/<user>/realmforge/` and open `config.php` for editing.
2. Replace the placeholder values with your real keys:

   ```php
   define('GROQ_API_KEY', 'gsk_YourActualGroqKeyHere');
   define('STABLE_DIFFUSION_API_KEY', 'sk-YourActualStabilityKeyHere');
   ```

3. While you're in the file, change the default admin credentials:

   ```php
   define('ADMIN_USER', 'myadmin');
   define('ADMIN_PASS', 'a-strong-random-password');
   ```

4. For production, confirm these two lines are set (they are by default):

   ```php
   ini_set('display_errors', 0);   // hide errors from visitors
   ini_set('log_errors', 1);       // write errors to logs/errors.log
   ```

5. **Save** the file.

> **Security:** `config.php` sits *outside* the public document root, so
> visitors cannot access it directly. Never move it into `public/`.

---

### Step 5 – Set Directory Permissions

RealmForge needs to write AI-generated images and log files at runtime.

**Via cPanel File Manager:**

1. Navigate to `/home/<user>/realmforge/`.
2. Right-click the `images` folder → **Change Permissions** → set to **`0755`** → check **Recurse into subdirectories**.
3. Repeat for the `logs` folder.
4. Repeat for the `database` folder (the engine writes `world.json` here on first run).

**Via Terminal:**

```bash
cd ~/realmforge
chmod -R 755 images/generated/
chmod -R 755 logs/
chmod -R 755 database/
```

> **Tip:** Some hosts run PHP via CGI/FastCGI under your own user account, so
> `755` is sufficient. If images still fail to save, try `775`. Avoid `777` in
> production.

---

### Step 6 – Protect the Admin Dashboard

The admin area at `/admin/dashboard.php` is protected by HTTP Basic Auth via
`admin/.htaccess`. You need to create a `.htpasswd` file and update the path.

**Via cPanel Terminal:**

```bash
# Create the password file (you will be prompted for a password)
mkdir -p ~/realmforge/.htpassfiles
htpasswd -c ~/realmforge/.htpassfiles/.htpasswd admin

# To add more users later, omit the -c flag (it overwrites the file):
# htpasswd ~/realmforge/.htpassfiles/.htpasswd anotheruser
```

Then edit `admin/.htaccess` in File Manager and update the `AuthUserFile` line:

```apache
AuthUserFile /home/<user>/realmforge/.htpassfiles/.htpasswd
```

**Via cPanel's "Directory Privacy" tool:**

1. In cPanel, go to **Directory Privacy** (under *Security*).
2. Navigate to `realmforge/admin/`.
3. Check **Password protect this directory**, give it a label, and save.
4. Add a user with a strong password.

> **Tip:** The "Directory Privacy" method automatically generates the
> `.htaccess` and `.htpasswd` entries for you—no command line needed.

---

### Step 7 – (Optional) Set Up MySQL for Save Games

RealmForge works entirely with JSON files by default. If you want persistent
database-backed save games:

1. In cPanel, open **MySQL® Databases**.
2. Create a new database (e.g. `<user>_realmforge`).
3. Create a new database user and assign it **ALL PRIVILEGES** on that database.
4. Open **phpMyAdmin** (cPanel → *Databases* → *phpMyAdmin*).
5. Select your new database in the left sidebar.
6. Click the **Import** tab → choose `database/schema.sql` from your local machine → click **Go**.
7. Add the connection details to `config.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', '<user>_realmforge');
   define('DB_USER', '<user>_rfuser');
   define('DB_PASS', 'your-db-password');
   ```

> **Tip:** On most cPanel hosts the database name and user are prefixed with
> your cPanel username (e.g. `cpuser_realmforge`). Use the exact names shown in
> the MySQL Databases screen.

---

### Step 8 – Verify the Installation

1. **Visit your domain** (e.g. `https://playrealmforge.co.uk`).
   - You should see the RealmForge game interface with the "Your adventure begins…" placeholder.
2. **Click an action** or type a custom command. If Groq is configured correctly you will receive an AI-narrated response within a few seconds.
3. **Check image generation.** Scene art should appear above the story text after a short delay. If it does not, verify your Stability AI key and that `images/generated/` is writable.
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
   | **Repository Path** | `/home/<user>/realmforge` |
   | **Repository Name** | `realmforge` |

5. Click **Create**. cPanel will clone the repo and run the `.cpanel.yml`
   deployment tasks automatically on the first pull.

> **Tip:** If the repository is private, add an access token to the Clone URL:
> `https://<token>@github.com/hostyorkshire/realmforge.git`

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
| `CPANEL_USERNAME` | `cpuser` | Your cPanel login username |
| `CPANEL_API_TOKEN` | `WBLY3E0JKH…` | The API token from Step 2 |
| `CPANEL_HOST` | `server.hostyorkshire.co.uk` | cPanel server hostname |
| `CPANEL_REPO_PATH` | `/home/cpuser/realmforge` | Full path to the repository on the server |

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
   - /usr/bin/mysql -u cpuser_rfuser -p"your-db-password" cpuser_realmforge < database/schema.sql
   ```

3. Commit and push the change; the next deploy will run the import
   automatically.

> **Security:** Database credentials in `.cpanel.yml` are stored in the private
> repository on the server. If your repository is public, consider using a
> separate deploy script that reads credentials from a non-committed file
> instead.

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
| **Blank page / white screen** | PHP fatal error hidden by `display_errors 0` | Check `logs/errors.log` or temporarily set `display_errors` to `1` in `config.php` |
| **"Failed to connect to api.groq.com"** | `curl` extension not enabled | Enable `curl` in *Select PHP Version* → *Extensions* |
| **Images not appearing** | `images/generated/` not writable | Re-check permissions (step 5); look for errors in `logs/errors.log` |
| **Admin page shows "401 Unauthorized"** | `.htpasswd` path wrong in `admin/.htaccess` | Run `realpath ~/realmforge/.htpassfiles/.htpasswd` in Terminal to get the correct absolute path |
| **World not generating** | `database/` directory not writable | `chmod 755 database/` or set permissions in File Manager |
| **API rate-limit errors** | Too many requests to Groq / Stability AI | Wait a minute and try again; check your plan's rate limits |

### Security Tips

- **Keep `config.php` outside the document root.** The recommended directory
  layout already does this—`public/` is the only folder exposed to the web.
- **Use HTTPS.** Enable a free SSL certificate via cPanel → *SSL/TLS Status*
  or *Let's Encrypt™* and force HTTPS in `public/.htaccess`.
- **Restrict `admin/` access** to your own IP address if possible by adding
  `Require ip <your-ip>` to `admin/.htaccess`.
- **Change the default admin password** in `config.php` before going live.
- **Disable directory listing** by ensuring `Options -Indexes` is present in
  your root `.htaccess` (or `public/.htaccess`).

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
