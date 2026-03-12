<?php
/**
 * Admin Dashboard
 * Overview of RealmForge statistics and quick links.
 */

require_once __DIR__ . '/../../config.php';

$logFiles = [
    'AI Requests'    => LOGS_PATH . '/ai_requests.log',
    'Player Actions' => LOGS_PATH . '/player_actions.log',
    'Errors'         => LOGS_PATH . '/errors.log',
];

$logCounts = [];
foreach ($logFiles as $label => $path) {
    if (file_exists($path)) {
        $logCounts[$label] = count(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    } else {
        $logCounts[$label] = 0;
    }
}

$worldExists = file_exists(WORLD_FILE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge Admin</title>
  <style>
    body { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1   { color: #c9a45c; margin-bottom: 1.5rem; }
    h2   { color: #6ab0f3; margin: 1.5rem 0 0.75rem; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
    .card { background: #161c24; border: 1px solid #2a3347; border-radius: 6px; padding: 1.25rem; }
    .card h3 { margin: 0 0 0.5rem; font-size: 0.85rem; color: #7a8494; text-transform: uppercase; }
    .card .value { font-size: 2rem; font-weight: 700; color: #c9a45c; }
    .nav { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .nav a { color: #6ab0f3; text-decoration: none; border: 1px solid #2a3347;
             padding: 0.4rem 0.9rem; border-radius: 4px; font-size: 0.9rem; }
    .nav a:hover { background: #161c24; }
    table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
    th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #2a3347; font-size: 0.85rem; }
    th { color: #6ab0f3; }
    .badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 3px; font-size: 0.75rem; }
    .badge.ok  { background: rgba(94,201,124,0.2); color: #5ec97c; }
    .badge.warn { background: rgba(224,82,82,0.2); color: #e05252; }
  </style>
</head>
<body>
  <h1>⚔ RealmForge Admin</h1>

  <nav class="nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="logs.php">Logs</a>
    <a href="quests.php">Quests</a>
    <a href="npcs.php">NPCs</a>
    <a href="factions.php">Factions</a>
    <a href="lore.php">Lore</a>
    <a href="images.php">Images</a>
    <a href="world.php">World</a>
  </nav>

  <h2>System Overview</h2>
  <div class="grid">
    <div class="card">
      <h3>World Generated</h3>
      <div class="value"><?= $worldExists ? '✓' : '✗' ?></div>
    </div>
    <div class="card">
      <h3>Image Generation</h3>
      <div class="value" style="font-size:1rem;color:#5ec97c;">Canvas</div>
    </div>
    <?php foreach ($logCounts as $label => $count): ?>
    <div class="card">
      <h3><?= htmlspecialchars($label) ?> Log</h3>
      <div class="value"><?= $count ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <h2>Configuration</h2>
  <table>
    <tr><th>Setting</th><th>Value</th><th>Status</th></tr>
    <tr>
      <td>Groq API Key</td>
      <td><?= GROQ_API_KEY === 'your-groq-api-key-here' ? '<em>Not set</em>' : '•••••••' ?></td>
      <td><span class="badge <?= GROQ_API_KEY !== 'your-groq-api-key-here' ? 'ok' : 'warn' ?>">
        <?= GROQ_API_KEY !== 'your-groq-api-key-here' ? 'Set' : 'Missing' ?>
      </span></td>
    </tr>
    <tr>
      <td>Image Generation</td>
      <td>HTML5 Canvas (browser-side)</td>
      <td><span class="badge ok">Active</span></td>
    </tr>
    <tr>
      <td>Groq Model</td>
      <td><?= htmlspecialchars(GROQ_MODEL) ?></td>
      <td><span class="badge ok">OK</span></td>
    </tr>
  </table>
</body>
</html>
