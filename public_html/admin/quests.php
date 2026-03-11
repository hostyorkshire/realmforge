<?php
/**
 * Admin – Quest Manager
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../engine/quests.php';

$quests = getAllQuests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge – Quests</title>
  <style>
    body { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1   { color: #c9a45c; }
    .nav a { color: #6ab0f3; margin-right: 1rem; font-size: 0.9rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #2a3347; font-size: 0.9rem; }
    th { color: #6ab0f3; }
    .reward { color: #c9a45c; }
  </style>
</head>
<body>
  <h1>📜 Quest Manager</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <table>
    <thead>
      <tr><th>ID</th><th>Title</th><th>Description</th><th>Reward</th><th>Faction</th></tr>
    </thead>
    <tbody>
      <?php foreach ($quests as $quest): ?>
      <tr>
        <td><?= htmlspecialchars($quest['id']) ?></td>
        <td><?= htmlspecialchars($quest['title']) ?></td>
        <td><?= htmlspecialchars($quest['description']) ?></td>
        <td class="reward"><?= htmlspecialchars($quest['reward_gold']) ?> gold + <?= htmlspecialchars($quest['reward_item']) ?></td>
        <td><?= htmlspecialchars($quest['faction']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
