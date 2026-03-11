<?php
/**
 * Admin – Log Viewer
 */

require_once __DIR__ . '/../../config.php';

$available = [
    'ai'     => 'ai_requests.log',
    'player' => 'player_actions.log',
    'errors' => 'errors.log',
];

$selected = isset($_GET['log']) && isset($available[$_GET['log']]) ? $_GET['log'] : 'player';
$logPath  = LOGS_PATH . '/' . $available[$selected];

$lines = [];
if (file_exists($logPath)) {
    $all   = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_slice(array_reverse($all), 0, 200);
}

// Clear log action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
    file_put_contents($logPath, '');
    header('Location: logs.php?log=' . $selected);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge Logs</title>
  <style>
    body { font-family: monospace; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1   { color: #c9a45c; font-family: sans-serif; }
    .nav { display: flex; gap: 1rem; margin: 1rem 0; flex-wrap: wrap; }
    .nav a { color: #6ab0f3; text-decoration: none; border: 1px solid #2a3347;
             padding: 0.3rem 0.8rem; border-radius: 4px; font-family: sans-serif; font-size: 0.85rem; }
    .nav a.active { background: #161c24; color: #c9a45c; border-color: #c9a45c; }
    .log-line { font-size: 0.8rem; padding: 0.2rem 0; border-bottom: 1px solid #1a2030; white-space: pre-wrap; word-break: break-all; }
    .log-line:nth-child(even) { background: #111720; }
    form { display: inline; }
    button { background: #e05252; color: white; border: none; padding: 0.4rem 0.9rem;
             border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-family: sans-serif; }
    .count { color: #7a8494; font-family: sans-serif; font-size: 0.85rem; margin-left: 1rem; }
  </style>
</head>
<body>
  <h1>⚔ Logs</h1>
  <nav class="nav">
    <a href="dashboard.php">← Dashboard</a>
    <a href="?log=player" class="<?= $selected === 'player' ? 'active' : '' ?>">Player Actions</a>
    <a href="?log=ai"     class="<?= $selected === 'ai'     ? 'active' : '' ?>">AI Requests</a>
    <a href="?log=errors" class="<?= $selected === 'errors' ? 'active' : '' ?>">Errors</a>
  </nav>

  <form method="post">
    <button type="submit" name="clear" onclick="return confirm('Clear this log?')">Clear Log</button>
  </form>
  <span class="count"><?= count($lines) ?> entries shown (max 200)</span>

  <div style="margin-top:1rem;">
    <?php if (empty($lines)): ?>
      <p style="color:#7a8494;">Log is empty.</p>
    <?php else: ?>
      <?php foreach ($lines as $line): ?>
        <div class="log-line"><?= htmlspecialchars($line) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
