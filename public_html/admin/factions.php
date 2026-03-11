<?php
/**
 * Admin – Faction Manager
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/factions.php';

$factions = getAllFactions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge – Factions</title>
  <style>
    body { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1   { color: #c9a45c; }
    .nav a { color: #6ab0f3; margin-right: 1rem; font-size: 0.9rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #2a3347; font-size: 0.9rem; }
    th { color: #6ab0f3; }
    .alignment { color: #7a8494; font-style: italic; }
  </style>
</head>
<body>
  <h1>🏴 Faction Manager</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <table>
    <thead>
      <tr><th>ID</th><th>Name</th><th>Alignment</th><th>Description</th></tr>
    </thead>
    <tbody>
      <?php foreach ($factions as $faction): ?>
      <tr>
        <td><?= htmlspecialchars($faction['id']) ?></td>
        <td><?= htmlspecialchars($faction['name']) ?></td>
        <td class="alignment"><?= htmlspecialchars($faction['alignment']) ?></td>
        <td><?= htmlspecialchars($faction['description']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
