<?php
/**
 * Admin – World Viewer
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../engine/world.php';

$world = loadWorld();

// Regenerate if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regenerate'])) {
    require_once __DIR__ . '/../engine/continentGenerator.php';
    $world = generateWorld();
    header('Location: world.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge – World</title>
  <style>
    body  { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1    { color: #c9a45c; }
    h2    { color: #6ab0f3; margin: 1.5rem 0 0.75rem; }
    .nav a { color: #6ab0f3; margin-right: 1rem; font-size: 0.9rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
    th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #2a3347; font-size: 0.85rem; }
    th { color: #6ab0f3; }
    button { background: #c9a45c; color: #0b0f14; border: none; padding: 0.5rem 1rem;
             border-radius: 4px; cursor: pointer; font-weight: 600; }
    .meta { color: #7a8494; font-size: 0.85rem; margin-top: 0.5rem; }
  </style>
</head>
<body>
  <h1>🌍 World Manager</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <?php if (!$world): ?>
    <p>No world generated yet.</p>
    <form method="post">
      <button type="submit" name="regenerate">Generate World</button>
    </form>
  <?php else: ?>
    <p class="meta">Generated: <?= htmlspecialchars($world['generated'] ?? 'unknown') ?> | Size: <?= $world['size'] ?? 50 ?>×<?= $world['size'] ?? 50 ?></p>

    <form method="post" style="margin-bottom:1rem;">
      <button type="submit" name="regenerate" onclick="return confirm('Regenerate world? This cannot be undone.')">Regenerate World</button>
    </form>

    <h2>Kingdoms</h2>
    <table>
      <thead><tr><th>ID</th><th>Name</th></tr></thead>
      <tbody>
        <?php foreach ($world['kingdoms'] ?? [] as $k): ?>
        <tr><td><?= htmlspecialchars($k['id']) ?></td><td><?= htmlspecialchars($k['name']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>Towns (<?= count($world['towns'] ?? []) ?>)</h2>
    <table>
      <thead><tr><th>Name</th><th>Type</th><th>Biome</th><th>Faction</th><th>Coords</th></tr></thead>
      <tbody>
        <?php foreach ($world['towns'] ?? [] as $t): ?>
        <tr>
          <td><?= htmlspecialchars($t['name']) ?></td>
          <td><?= htmlspecialchars($t['type']) ?></td>
          <td><?= htmlspecialchars($t['biome']) ?></td>
          <td><?= htmlspecialchars($t['faction']) ?></td>
          <td><?= $t['x'] ?>,<?= $t['y'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>Dungeons (<?= count($world['dungeons'] ?? []) ?>)</h2>
    <table>
      <thead><tr><th>Name</th><th>Type</th><th>Coords</th></tr></thead>
      <tbody>
        <?php foreach ($world['dungeons'] ?? [] as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['name']) ?></td>
          <td><?= htmlspecialchars($d['type']) ?></td>
          <td><?= $d['x'] ?>,<?= $d['y'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
