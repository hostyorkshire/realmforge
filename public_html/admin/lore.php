<?php
/**
 * Admin – Lore Viewer
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../engine/lore.php';

$lore = getAllLore();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge – Lore</title>
  <style>
    body { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1   { color: #c9a45c; }
    .nav a { color: #6ab0f3; margin-right: 1rem; font-size: 0.9rem; }
    .lore-entry { background: #161c24; border: 1px solid #2a3347; border-radius: 6px;
                  padding: 1rem; margin-bottom: 1rem; }
    .lore-entry h3 { color: #6ab0f3; margin-bottom: 0.5rem; font-size: 0.9rem; text-transform: uppercase; }
    .lore-entry p { line-height: 1.7; font-size: 0.9rem; }
  </style>
</head>
<body>
  <h1>📖 World Lore</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <?php foreach ($lore as $key => $text): ?>
  <div class="lore-entry">
    <h3><?= htmlspecialchars(str_replace('_', ' ', $key)) ?></h3>
    <p><?= htmlspecialchars($text) ?></p>
  </div>
  <?php endforeach; ?>
</body>
</html>
