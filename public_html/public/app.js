/**
 * RealmForge – Frontend Application
 * Handles player input, API communication, and UI updates.
 */

'use strict';

// ── State ─────────────────────────────────────────────────────────────────────

const DEFAULT_STATE = {
  location: 'stonebridge_village',
  health: 100,
  gold: 25,
  inventory: ['Torch'],
  quests: [],
  faction_reputation: {
    kingdom_avaros:     0,
    iron_dominion:      0,
    northern_clans:     0,
    shadow_brotherhood: 0,
    temple_of_dawn:     0,
  },
  history: [],
  story_summary: '',
};

let gameState = loadState() || structuredClone(DEFAULT_STATE);
let isLoading = false;

// ── DOM References ────────────────────────────────────────────────────────────

const storyText     = document.getElementById('storyText');
const sceneCanvas   = document.getElementById('sceneCanvas');
const choices       = [0, 1, 2].map(i => document.getElementById(`choice${i}`));
const customCommand = document.getElementById('customCommand');
const commandSubmit = document.getElementById('commandSubmit');
const loadingOverlay = document.getElementById('loadingOverlay');
const toast         = document.getElementById('toast');
const statusLocation = document.getElementById('statusLocation');
const statusHealth  = document.getElementById('statusHealth');
const statusGold    = document.getElementById('statusGold');
const inventoryList = document.getElementById('inventoryList');
const questList     = document.getElementById('questList');
const factionList   = document.getElementById('factionList');
const mapCanvas     = document.getElementById('mapCanvas');

// ── Initialise ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
  updateUI();
  drawMap();
  drawSceneCanvas(sceneCanvas, gameState.location, '');
  attachEventListeners();
});

function attachEventListeners() {
  choices.forEach(btn => {
    btn.addEventListener('click', () => handleAction(btn.textContent.trim()));
  });

  commandSubmit.addEventListener('click', submitCustomCommand);

  customCommand.addEventListener('keydown', e => {
    if (e.key === 'Enter') submitCustomCommand();
  });
}

// ── Action Handling ───────────────────────────────────────────────────────────

function submitCustomCommand() {
  const cmd = customCommand.value.trim();
  if (!cmd) return;
  if (cmd.length > 200) {
    showToast('Command too long (max 200 characters)');
    return;
  }
  customCommand.value = '';
  handleAction(cmd);
}

async function handleAction(action) {
  if (isLoading) return;
  setLoading(true);

  try {
    const response = await fetch('../api/adventure.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, state: gameState }),
    });

    if (!response.ok) {
      const err = await response.json().catch(() => ({ error: 'Unknown error' }));
      throw new Error(err.error || `HTTP ${response.status}`);
    }

    const data = await response.json();

    // Update state
    gameState = data.state || gameState;

    // Update story
    displayNarrative(data.narrative || 'The world holds its breath…');

    // Update choices
    updateChoices(data.choices || []);

    // Show combat info if present
    if (data.combat_info) {
      showToast(data.combat_info);
    }

    // Generate scene illustration via HTML5 Canvas
    drawSceneCanvas(sceneCanvas, gameState.location, action);

    // Persist state
    saveState(gameState);

    // Update UI panels
    updateUI();

  } catch (err) {
    console.error('Adventure error:', err);
    showToast(`Error: ${err.message}`);
  } finally {
    setLoading(false);
  }
}

// ── Narrative Display ─────────────────────────────────────────────────────────

function displayNarrative(text) {
  storyText.classList.remove('fade-in');
  // Trigger reflow for re-animation
  void storyText.offsetWidth;
  storyText.innerHTML = `<p>${escapeHtml(text)}</p>`;
  storyText.classList.add('fade-in');
}

// ── Choices ───────────────────────────────────────────────────────────────────

function updateChoices(newChoices) {
  const defaults = ['Look around carefully', 'Continue exploring', 'Rest and recover'];
  for (let i = 0; i < 3; i++) {
    choices[i].textContent = newChoices[i] || defaults[i];
    choices[i].disabled = false;
  }
}

// ── Scene Canvas (HTML5 procedural illustration) ──────────────────────────────

const DUNGEON_LOCATIONS = new Set([
  'crypt_of_shadows', 'goblin_cave', 'forgotten_catacombs', 'dragon_lair',
]);

const TOWN_LOCATIONS = new Set([
  'stonebridge_village', 'ravenmoor_town', 'ironpeak_settlement', 'dawn_harbour',
]);

// Biomes that render a night-time sky.
const NIGHT_BIOMES = new Set(['swamp', 'mountains']);

/**
 * Deterministic seeded pseudo-random based on a string key.
 * Returns a function that yields floats in [0, 1).
 */
function makeRng(seed) {
  let h = 0;
  for (let i = 0; i < seed.length; i++) {
    h = (Math.imul(31, h) + seed.charCodeAt(i)) | 0;
  }
  return function () {
    h ^= h << 13;
    h ^= h >> 17;
    h ^= h << 5;
    return ((h >>> 0) / 0x100000000);
  };
}

/** Classify location into a scene category. */
function getSceneType(location) {
  if ([...DUNGEON_LOCATIONS].some(d => location === d || location.includes(d))) return 'dungeon';
  if ([...TOWN_LOCATIONS].some(t => location === t || location.includes(t)))    return 'town';
  return 'wilderness';
}

/** Derive a biome for a location using its known coordinates. */
function getLocationBiome(location) {
  const coords = LOCATION_COORDS[location];
  if (!coords) return 'forest';
  return clientSelectBiome(coords.x, coords.y, 50);
}

/**
 * Draw a procedural scene illustration on the given canvas.
 * Deterministic for the same (location, action) pair.
 */
function drawSceneCanvas(canvas, location, action) {
  const ctx    = canvas.getContext('2d');
  const W      = canvas.width;
  const H      = canvas.height;
  const rng    = makeRng(location + '|' + action);
  const type   = getSceneType(location);
  const biome  = getLocationBiome(location);

  ctx.clearRect(0, 0, W, H);

  if (type === 'dungeon') {
    drawDungeonScene(ctx, W, H, rng);
  } else if (type === 'town') {
    drawTownScene(ctx, W, H, rng, biome);
  } else {
    drawWildernessScene(ctx, W, H, rng, biome);
  }
}

// ── Scene renderers ──────────────────────────────────────────────────────────

function drawDungeonScene(ctx, W, H, rng) {
  // Dark stone background
  const bg = ctx.createLinearGradient(0, 0, 0, H);
  bg.addColorStop(0, '#0a0a0e');
  bg.addColorStop(1, '#1a1520');
  ctx.fillStyle = bg;
  ctx.fillRect(0, 0, W, H);

  // Stone floor
  ctx.fillStyle = '#2a2430';
  ctx.fillRect(0, H * 0.65, W, H * 0.35);

  // Floor cracks / tiles
  ctx.strokeStyle = '#1a1420';
  ctx.lineWidth = 1;
  for (let x = 0; x < W; x += 40 + rng() * 20) {
    ctx.beginPath();
    ctx.moveTo(x, H * 0.65);
    ctx.lineTo(x + rng() * 10 - 5, H);
    ctx.stroke();
  }
  for (let y = H * 0.65; y < H; y += 20 + rng() * 15) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(W, y + rng() * 6 - 3);
    ctx.stroke();
  }

  // Stone wall texture (rough blocks)
  ctx.strokeStyle = '#25202e';
  ctx.lineWidth = 2;
  for (let row = 0; row < 4; row++) {
    const y = row * (H * 0.65 / 4);
    const offset = row % 2 === 0 ? 0 : 30;
    for (let x = offset; x < W; x += 60 + rng() * 10) {
      ctx.strokeRect(x, y, 58 + rng() * 8, H * 0.65 / 4 - 2);
    }
  }

  // Torches
  const torchPositions = [W * 0.2, W * 0.5, W * 0.8];
  torchPositions.forEach(tx => {
    const ty = H * 0.35 + rng() * (H * 0.1);
    drawTorch(ctx, tx, ty, rng);
  });

  // Archway / doorway
  ctx.fillStyle = '#050508';
  ctx.beginPath();
  ctx.moveTo(W * 0.42, H * 0.65);
  ctx.lineTo(W * 0.42, H * 0.25);
  ctx.quadraticCurveTo(W * 0.5, H * 0.12, W * 0.58, H * 0.25);
  ctx.lineTo(W * 0.58, H * 0.65);
  ctx.closePath();
  ctx.fill();

  // Stone arch border
  ctx.strokeStyle = '#3a3048';
  ctx.lineWidth = 4;
  ctx.beginPath();
  ctx.moveTo(W * 0.42, H * 0.65);
  ctx.lineTo(W * 0.42, H * 0.25);
  ctx.quadraticCurveTo(W * 0.5, H * 0.12, W * 0.58, H * 0.25);
  ctx.lineTo(W * 0.58, H * 0.65);
  ctx.stroke();

  // Atmospheric mist at floor level
  const mist = ctx.createRadialGradient(W / 2, H * 0.67, 10, W / 2, H * 0.67, W * 0.5);
  mist.addColorStop(0, 'rgba(100,80,140,0.18)');
  mist.addColorStop(1, 'rgba(100,80,140,0)');
  ctx.fillStyle = mist;
  ctx.fillRect(0, H * 0.55, W, H * 0.2);
}

function drawTorch(ctx, x, y, rng) {
  // Bracket
  ctx.fillStyle = '#5a4a30';
  ctx.fillRect(x - 3, y, 6, 18);

  // Flame (animated-look via layered gradient)
  const flameH = 22 + rng() * 8;
  const flame  = ctx.createRadialGradient(x, y - flameH * 0.3, 2, x, y, flameH * 0.7);
  flame.addColorStop(0, 'rgba(255,220,80,0.95)');
  flame.addColorStop(0.4, 'rgba(255,120,20,0.7)');
  flame.addColorStop(1, 'rgba(200,60,0,0)');
  ctx.fillStyle = flame;
  ctx.beginPath();
  ctx.ellipse(x, y - flameH * 0.4, 8, flameH * 0.6, 0, 0, Math.PI * 2);
  ctx.fill();

  // Glow on stone
  const glow = ctx.createRadialGradient(x, y, 5, x, y, 60);
  glow.addColorStop(0, 'rgba(255,160,50,0.18)');
  glow.addColorStop(1, 'rgba(255,160,50,0)');
  ctx.fillStyle = glow;
  ctx.fillRect(x - 60, y - 60, 120, 120);
}

function drawTownScene(ctx, W, H, rng, biome) {
  // Sky
  drawSky(ctx, W, H, rng, biome);

  // Ground
  const groundColor = biome === 'desert' ? '#c8a64a' : biome === 'swamp' ? '#3a5a3a' : '#5a7a30';
  ctx.fillStyle = groundColor;
  ctx.fillRect(0, H * 0.65, W, H * 0.35);

  // Road
  ctx.fillStyle = '#8a7a60';
  ctx.beginPath();
  ctx.moveTo(W * 0.38, H * 0.65);
  ctx.lineTo(W * 0.62, H * 0.65);
  ctx.lineTo(W * 0.75, H);
  ctx.lineTo(W * 0.25, H);
  ctx.closePath();
  ctx.fill();

  // Buildings (2–4)
  const buildingCount = 2 + Math.floor(rng() * 3);
  const spacing = W / (buildingCount + 1);
  for (let i = 0; i < buildingCount; i++) {
    const bx = spacing * (i + 1) - 40 + rng() * 20;
    const bh = 80 + rng() * 60;
    const bw = 55 + rng() * 30;
    drawBuilding(ctx, bx, H * 0.65, bw, bh, rng, biome);
  }

  // Foreground foliage / fence
  drawFoliageStrip(ctx, W, H, rng, biome);
}

function drawBuilding(ctx, x, groundY, w, h, rng, biome) {
  const wallColor  = biome === 'desert' ? '#d4a86a' : biome === 'swamp' ? '#4a5a40' : '#8a7a60';
  const roofColor  = biome === 'desert' ? '#b07840' : '#4a3020';
  const doorColor  = '#2a1a10';
  const winColor   = 'rgba(255,200,80,0.4)';

  // Wall
  ctx.fillStyle = wallColor;
  ctx.fillRect(x, groundY - h, w, h);

  // Roof (triangle)
  ctx.fillStyle = roofColor;
  ctx.beginPath();
  ctx.moveTo(x - 5, groundY - h);
  ctx.lineTo(x + w / 2, groundY - h - 30 - rng() * 20);
  ctx.lineTo(x + w + 5, groundY - h);
  ctx.closePath();
  ctx.fill();

  // Door
  const dw = w * 0.25;
  const dh = h * 0.4;
  ctx.fillStyle = doorColor;
  ctx.fillRect(x + w / 2 - dw / 2, groundY - dh, dw, dh);
  ctx.beginPath();
  ctx.arc(x + w / 2, groundY - dh, dw / 2, Math.PI, 0, false);
  ctx.fill();

  // Window
  ctx.fillStyle = winColor;
  ctx.fillRect(x + w * 0.65, groundY - h * 0.7, w * 0.2, h * 0.2);
  ctx.strokeStyle = '#5a4030';
  ctx.lineWidth = 2;
  ctx.strokeRect(x + w * 0.65, groundY - h * 0.7, w * 0.2, h * 0.2);
}

function drawFoliageStrip(ctx, W, H, rng, biome) {
  const leafColor = biome === 'swamp' ? '#2a4a20' : biome === 'desert' ? '#8a7a40' : '#2a5a18';
  for (let i = 0; i < 12; i++) {
    const fx = rng() * W;
    const fy = H * 0.65 + rng() * (H * 0.12);
    const fr = 12 + rng() * 18;
    ctx.fillStyle = leafColor;
    ctx.beginPath();
    ctx.arc(fx, fy, fr, 0, Math.PI * 2);
    ctx.fill();
  }
}

function drawWildernessScene(ctx, W, H, rng, biome) {
  drawSky(ctx, W, H, rng, biome);

  // Ground layer
  const groundColors = {
    forest:    '#2d5a27',
    plains:    '#6b8e3a',
    mountains: '#5a5a6a',
    desert:    '#c8a64a',
    swamp:     '#3a5a3a',
    coast:     '#3a5a7a',
  };
  ctx.fillStyle = groundColors[biome] || '#4a7a30';
  ctx.fillRect(0, H * 0.58, W, H * 0.42);

  // Biome-specific mid-ground features
  if (biome === 'forest' || biome === 'swamp') {
    drawTrees(ctx, W, H, rng, biome);
  } else if (biome === 'mountains') {
    drawMountains(ctx, W, H, rng);
  } else if (biome === 'desert') {
    drawDunes(ctx, W, H, rng);
  } else if (biome === 'coast') {
    drawWater(ctx, W, H, rng);
  } else {
    drawGrassland(ctx, W, H, rng);
  }

  // Path / road
  ctx.strokeStyle = 'rgba(180,160,100,0.5)';
  ctx.lineWidth = 6;
  ctx.beginPath();
  ctx.moveTo(W * 0.48, H);
  ctx.quadraticCurveTo(W * 0.5, H * 0.7, W * 0.55 + rng() * 60 - 30, H * 0.58);
  ctx.stroke();
}

function drawSky(ctx, W, H, rng, biome) {
  const isNight = NIGHT_BIOMES.has(biome);
  let sky;
  if (isNight) {
    sky = ctx.createLinearGradient(0, 0, 0, H * 0.6);
    sky.addColorStop(0, '#06080e');
    sky.addColorStop(1, '#1a2030');
  } else {
    sky = ctx.createLinearGradient(0, 0, 0, H * 0.6);
    sky.addColorStop(0, '#1a2a4a');
    sky.addColorStop(0.5, '#2a4a7a');
    sky.addColorStop(1, '#5a7aaa');
  }
  ctx.fillStyle = sky;
  ctx.fillRect(0, 0, W, H * 0.6);

  // Stars or clouds
  if (isNight) {
    for (let i = 0; i < 60; i++) {
      const sx = rng() * W;
      const sy = rng() * H * 0.5;
      const sr = 0.5 + rng() * 1.5;
      ctx.fillStyle = `rgba(255,255,255,${0.4 + rng() * 0.6})`;
      ctx.beginPath();
      ctx.arc(sx, sy, sr, 0, Math.PI * 2);
      ctx.fill();
    }
    // Moon
    ctx.fillStyle = '#d0d8e0';
    ctx.beginPath();
    ctx.arc(W * 0.8, H * 0.15 + rng() * H * 0.1, 18 + rng() * 8, 0, Math.PI * 2);
    ctx.fill();
  } else {
    // Sun
    const sunX = W * (0.6 + rng() * 0.3);
    const sunY = H * (0.08 + rng() * 0.1);
    const sunG = ctx.createRadialGradient(sunX, sunY, 4, sunX, sunY, 40);
    sunG.addColorStop(0, 'rgba(255,240,180,1)');
    sunG.addColorStop(0.4, 'rgba(255,200,80,0.6)');
    sunG.addColorStop(1, 'rgba(255,180,40,0)');
    ctx.fillStyle = sunG;
    ctx.beginPath();
    ctx.arc(sunX, sunY, 40, 0, Math.PI * 2);
    ctx.fill();

    // Clouds
    for (let i = 0; i < 3; i++) {
      drawCloud(ctx, rng() * W, H * 0.1 + rng() * H * 0.2, 40 + rng() * 40, rng);
    }
  }
}

function drawCloud(ctx, x, y, r, rng) {
  ctx.fillStyle = 'rgba(255,255,255,0.18)';
  for (let i = 0; i < 4; i++) {
    ctx.beginPath();
    ctx.arc(x + i * r * 0.6, y + rng() * r * 0.4, r * (0.5 + rng() * 0.5), 0, Math.PI * 2);
    ctx.fill();
  }
}

function drawTrees(ctx, W, H, rng, biome) {
  const trunkColor = biome === 'swamp' ? '#2a1a10' : '#3a2010';
  const leafColor  = biome === 'swamp' ? '#2a4a20' : '#1a4a10';
  const count = 8 + Math.floor(rng() * 8);
  for (let i = 0; i < count; i++) {
    const tx = rng() * W;
    const th = 60 + rng() * 80;
    const ty = H * 0.58 - th * 0.1 + rng() * (H * 0.08);
    // Trunk
    ctx.fillStyle = trunkColor;
    ctx.fillRect(tx - 5, ty, 10, th * 0.4);
    // Canopy layers
    for (let layer = 0; layer < 3; layer++) {
      const lr = (th * 0.35) * (1 - layer * 0.25);
      ctx.fillStyle = layer === 0 ? leafColor : '#2a6a18';
      ctx.beginPath();
      ctx.moveTo(tx, ty - layer * th * 0.2 - th * 0.1);
      ctx.lineTo(tx - lr, ty + th * 0.2 - layer * th * 0.1);
      ctx.lineTo(tx + lr, ty + th * 0.2 - layer * th * 0.1);
      ctx.closePath();
      ctx.fill();
    }
  }
}

function drawMountains(ctx, W, H, rng) {
  const peaks = 5 + Math.floor(rng() * 4);
  for (let i = 0; i < peaks; i++) {
    const px  = (i / peaks) * W + rng() * (W / peaks);
    const ph  = H * (0.25 + rng() * 0.3);
    const pw  = W * (0.12 + rng() * 0.12);
    const col = `hsl(220,${10 + Math.floor(rng() * 20)}%,${20 + Math.floor(rng() * 20)}%)`;
    ctx.fillStyle = col;
    ctx.beginPath();
    ctx.moveTo(px, H * 0.58 - ph);
    ctx.lineTo(px - pw, H * 0.58);
    ctx.lineTo(px + pw, H * 0.58);
    ctx.closePath();
    ctx.fill();
    // Snow cap
    ctx.fillStyle = 'rgba(230,240,255,0.7)';
    ctx.beginPath();
    ctx.moveTo(px, H * 0.58 - ph);
    ctx.lineTo(px - pw * 0.2, H * 0.58 - ph * 0.75);
    ctx.lineTo(px + pw * 0.2, H * 0.58 - ph * 0.75);
    ctx.closePath();
    ctx.fill();
  }
}

function drawDunes(ctx, W, H, rng) {
  for (let i = 0; i < 5; i++) {
    const dx = rng() * W;
    ctx.fillStyle = `rgba(200,160,60,${0.3 + rng() * 0.3})`;
    ctx.beginPath();
    ctx.ellipse(dx, H * 0.58, 80 + rng() * 120, 30 + rng() * 20, 0, Math.PI, 0, true);
    ctx.fill();
  }
}

function drawWater(ctx, W, H, rng) {
  const water = ctx.createLinearGradient(0, H * 0.58, 0, H);
  water.addColorStop(0, '#2a5a8a');
  water.addColorStop(1, '#1a3a6a');
  ctx.fillStyle = water;
  ctx.fillRect(0, H * 0.58, W, H * 0.42);
  // Waves
  ctx.strokeStyle = 'rgba(100,180,255,0.3)';
  ctx.lineWidth = 2;
  for (let i = 0; i < 6; i++) {
    const wy = H * 0.62 + i * (H * 0.05);
    ctx.beginPath();
    for (let x = 0; x < W; x += 20) {
      const amp = 4 + rng() * 4;
      ctx.lineTo(x, wy + Math.sin((x / W) * Math.PI * 4 + rng()) * amp);
    }
    ctx.stroke();
  }
}

function drawGrassland(ctx, W, H, rng) {
  // Rolling hills
  for (let i = 0; i < 3; i++) {
    ctx.fillStyle = `hsl(100,${35 + i * 8}%,${28 + i * 6}%)`;
    ctx.beginPath();
    ctx.moveTo(0, H * 0.58);
    for (let x = 0; x <= W; x += 20) {
      ctx.lineTo(x, H * 0.58 - Math.sin((x / W) * Math.PI + i) * (20 + rng() * 20) - i * 15);
    }
    ctx.lineTo(W, H);
    ctx.lineTo(0, H);
    ctx.closePath();
    ctx.fill();
  }
}

// ── UI Updates ────────────────────────────────────────────────────────────────

function updateUI() {
  updateStatusBar();
  updateInventory();
  updateQuests();
  updateFactions();
}

function updateStatusBar() {
  const loc = (gameState.location || 'unknown').replace(/_/g, ' ');
  statusLocation.textContent = `📍 ${capitalise(loc)}`;
  statusHealth.textContent   = `❤️ ${gameState.health ?? 100} HP`;
  statusGold.textContent     = `💰 ${gameState.gold ?? 0} Gold`;
}

function updateInventory() {
  const items = gameState.inventory || [];
  if (items.length === 0) {
    inventoryList.innerHTML = '<li class="item-entry empty">Nothing carried</li>';
    return;
  }
  inventoryList.innerHTML = items
    .map(item => `<li class="item-entry">${escapeHtml(item)}</li>`)
    .join('');
}

function updateQuests() {
  const quests = gameState.quests || [];
  if (quests.length === 0) {
    questList.innerHTML = '<li class="item-entry quest-none">No active quests</li>';
    return;
  }
  questList.innerHTML = quests
    .map(q => `<li class="item-entry">${escapeHtml(q.title || q)}</li>`)
    .join('');
}

function updateFactions() {
  const rep = gameState.faction_reputation || {};
  const factionNames = {
    kingdom_avaros:     'Kingdom of Avaros',
    iron_dominion:      'Iron Dominion',
    northern_clans:     'Northern Clans',
    shadow_brotherhood: 'Shadow Brotherhood',
    temple_of_dawn:     'Temple of Dawn',
  };

  factionList.innerHTML = Object.entries(factionNames)
    .map(([id, name]) => {
      const score = rep[id] ?? 0;
      const tier  = getReputationTier(score);
      const cls   = getRepClass(score);
      return `<li class="item-entry faction-entry">
        <span>${escapeHtml(name)}</span>
        <span class="faction-rep ${cls}">${tier}</span>
      </li>`;
    })
    .join('');
}

// ── Mini Map ──────────────────────────────────────────────────────────────────

const BIOME_COLORS = {
  forest:    '#2d5a27',
  plains:    '#6b8e3a',
  mountains: '#6a6a7a',
  desert:    '#c8a64a',
  swamp:     '#3a5a3a',
  coast:     '#2a4a8a',
};

function drawMap() {
  const ctx    = mapCanvas.getContext('2d');
  const size   = 50;
  const canvasSize = 220;
  const cellSize   = canvasSize / size;

  ctx.fillStyle = '#0b0f14';
  ctx.fillRect(0, 0, canvasSize, canvasSize);

  // Draw deterministic tiles (mirrors server-side biome logic)
  for (let y = 0; y < size; y++) {
    for (let x = 0; x < size; x++) {
      const biome = clientSelectBiome(x, y, size);
      ctx.fillStyle = BIOME_COLORS[biome] || '#333';
      ctx.fillRect(x * cellSize, y * cellSize, cellSize, cellSize);
    }
  }

  // Draw player position
  const pos = locationToCoords(gameState.location);
  if (pos) {
    ctx.fillStyle = '#c9a45c';
    ctx.beginPath();
    ctx.arc(
      pos.x * cellSize + cellSize / 2,
      pos.y * cellSize + cellSize / 2,
      Math.max(2, cellSize * 1.5),
      0, Math.PI * 2
    );
    ctx.fill();
  }
}

function clientSelectBiome(x, y, size) {
  // Large prime multipliers matching server-side continentGenerator.php for deterministic output
  // Use modulo 0x80000000 to replicate PHP's & 0x7FFFFFFF on 64-bit integers
  const hash = ((x * 374761393) + (y * 668265263)) % 0x80000000;
  const normalized = hash / 0x7FFFFFFF;

  if (x < 3 || x > size - 4 || y < 3 || y > size - 4) return 'coast';
  if (y < size * 0.2) return normalized < 0.6 ? 'mountains' : 'forest';
  if (x > size * 0.7 && y > size * 0.3) return normalized < 0.5 ? 'desert' : 'plains';
  if (x < size * 0.3 && y > size * 0.6) return normalized < 0.4 ? 'swamp' : 'forest';

  const biomes = ['forest', 'forest', 'plains', 'plains', 'forest'];
  return biomes[hash % biomes.length];
}

const LOCATION_COORDS = {
  stonebridge_village:  { x: 15, y: 21 },
  ravenmoor_town:       { x: 28, y: 33 },
  ironpeak_settlement:  { x: 38, y: 12 },
  dawn_harbour:         { x: 8,  y: 40 },
  crypt_of_shadows:     { x: 28, y: 17 },
  goblin_cave:          { x: 20, y: 25 },
  forgotten_catacombs:  { x: 42, y: 30 },
  dragon_lair:          { x: 45, y: 8  },
};

function locationToCoords(location) {
  return LOCATION_COORDS[location] || null;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function getReputationTier(score) {
  if (score >= 75)  return 'Revered';
  if (score >= 50)  return 'Honoured';
  if (score >= 25)  return 'Friendly';
  if (score >= 0)   return 'Neutral';
  if (score >= -25) return 'Unfriendly';
  if (score >= -50) return 'Hostile';
  return 'Hated';
}

function getRepClass(score) {
  if (score >= 50)  return 'honoured';
  if (score >= 25)  return 'friendly';
  if (score >= 0)   return 'neutral';
  return 'hostile';
}

function capitalise(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}

// ── Loading State ─────────────────────────────────────────────────────────────

function setLoading(loading) {
  isLoading = loading;
  loadingOverlay.classList.toggle('hidden', !loading);
  choices.forEach(btn => { btn.disabled = loading; });
  commandSubmit.disabled = loading;
}

// ── Toast Notifications ───────────────────────────────────────────────────────

function showToast(message, duration = 4000) {
  toast.textContent = message;
  toast.classList.remove('hidden');
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.classList.add('hidden'), 300);
  }, duration);
}

// ── Persistence ───────────────────────────────────────────────────────────────

function saveState(state) {
  try {
    localStorage.setItem('realmforge_state', JSON.stringify(state));
  } catch {
    // Storage may be unavailable; silently ignore
  }
}

function loadState() {
  try {
    const raw = localStorage.getItem('realmforge_state');
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

// ── Text-to-Speech (TTS) ──────────────────────────────────────────────────────
// Uses the browser-native Web Speech API — free, no third-party services needed.
// Reads only the story narrative (#storyText) and the three choice button labels.
// To remove this feature: delete this block and the ttsControls markup in index.html.

(function initTTS() {
  const playBtn = document.getElementById('ttsPlay');
  const stopBtn = document.getElementById('ttsStop');

  // Hide controls if the browser does not support the Web Speech API
  if (!('speechSynthesis' in window)) {
    const wrap = document.getElementById('ttsControls');
    if (wrap) wrap.style.display = 'none';
    return;
  }

  /** Collect readable text: story narrative then the three choice labels. */
  function buildReadText() {
    const storyEl = document.getElementById('storyText');
    const storyPart = storyEl ? storyEl.innerText.trim() : '';
    const choiceBtns = document.querySelectorAll('#choicesPanel .choice-btn');
    const choicePart = Array.from(choiceBtns)
      .map((btn, i) => `Option ${i + 1}: ${btn.textContent.trim()}`)
      .join('. ');
    return storyPart
      ? `${storyPart}. Your choices are: ${choicePart}`
      : choicePart;
  }

  function resetButtons() {
    playBtn.disabled = false;
    stopBtn.disabled = true;
  }

  playBtn.addEventListener('click', () => {
    speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(buildReadText());
    utterance.onend  = resetButtons;
    utterance.onerror = resetButtons;
    playBtn.disabled = true;
    stopBtn.disabled = false;
    speechSynthesis.speak(utterance);
  });

  stopBtn.addEventListener('click', () => {
    speechSynthesis.cancel();
    resetButtons();
  });
}());
