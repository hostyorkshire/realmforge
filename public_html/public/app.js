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
const sceneImage    = document.getElementById('sceneImage');
const scenePlaceholder = document.getElementById('scenePlaceholder');
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

    // Request scene image
    requestSceneImage(gameState.location, action);

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

// ── Scene Image ───────────────────────────────────────────────────────────────

async function requestSceneImage(location, situation) {
  try {
    const response = await fetch('../api/generateImage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: 'scene', context: location, subtype: situation }),
    });

    if (!response.ok) return;

    const data = await response.json();
    if (data.image_url) {
      crossfadeScene(data.image_url);
    }
  } catch {
    // Image generation is optional; silently fail
  }
}

function crossfadeScene(url) {
  const img = new Image();
  img.onload = () => {
    sceneImage.style.opacity = '0';
    sceneImage.src = url;
    sceneImage.classList.remove('hidden');
    scenePlaceholder.classList.add('hidden');
    requestAnimationFrame(() => {
      sceneImage.style.transition = 'opacity 0.6s ease';
      sceneImage.style.opacity = '1';
    });
  };
  img.src = url;
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
