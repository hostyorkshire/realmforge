<?php
/**
 * Admin – NPC Manager
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../engine/npcs.php';

$npcs = getAllNpcs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge – NPCs</title>
  <style>
    body { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1   { color: #c9a45c; }
    .nav a { color: #6ab0f3; margin-right: 1rem; font-size: 0.9rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #2a3347; font-size: 0.9rem; }
    th { color: #6ab0f3; }
    .personality { color: #7a8494; font-style: italic; }
  </style>
</head>
<body>
  <h1>🧙 NPC Manager</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <table>
    <thead>
      <tr><th>ID</th><th>Name</th><th>Role</th><th>Location</th><th>Personality</th></tr>
    </thead>
    <tbody>
      <?php foreach ($npcs as $npc): ?>
      <tr>
        <td><?= htmlspecialchars($npc['id']) ?></td>
        <td><?= htmlspecialchars($npc['name']) ?></td>
        <td><?= htmlspecialchars($npc['role']) ?></td>
        <td><?= htmlspecialchars($npc['location']) ?></td>
        <td class="personality"><?= htmlspecialchars($npc['personality']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
