<?php
/**
 * Admin – Image Cache Manager
 */

require_once __DIR__ . '/../config.php';

$subDirs = ['scenes', 'npcs', 'monsters', 'items', 'towns', 'dungeons', 'maps'];
$counts  = [];
$total   = 0;

foreach ($subDirs as $dir) {
    $path = IMAGES_PATH . '/' . $dir;
    $files = glob($path . '/*.png') ?: [];
    $counts[$dir] = count($files);
    $total += count($files);
}

// Handle cache clear
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_dir'])) {
    $clearDir = $_POST['clear_dir'];
    if (in_array($clearDir, $subDirs, true)) {
        $files = glob(IMAGES_PATH . '/' . $clearDir . '/*.png') ?: [];
        foreach ($files as $file) {
            unlink($file);
        }
    }
    header('Location: images.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RealmForge – Images</title>
  <style>
    body  { font-family: sans-serif; background: #0b0f14; color: #d4c9b8; margin: 0; padding: 2rem; }
    h1    { color: #c9a45c; }
    .nav a { color: #6ab0f3; margin-right: 1rem; font-size: 0.9rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #2a3347; font-size: 0.9rem; }
    th { color: #6ab0f3; }
    button { background: #e05252; color: white; border: none; padding: 0.3rem 0.7rem;
             border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
    .total { color: #c9a45c; font-size: 1.1rem; margin: 1rem 0; }
  </style>
</head>
<body>
  <h1>🖼 Image Cache</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <p class="total">Total cached images: <?= $total ?></p>

  <table>
    <thead>
      <tr><th>Directory</th><th>Images</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php foreach ($counts as $dir => $count): ?>
      <tr>
        <td><?= htmlspecialchars($dir) ?></td>
        <td><?= $count ?></td>
        <td>
          <?php if ($count > 0): ?>
          <form method="post" style="display:inline;">
            <input type="hidden" name="clear_dir" value="<?= htmlspecialchars($dir) ?>" />
            <button type="submit" onclick="return confirm('Clear <?= htmlspecialchars($dir) ?> cache?')">Clear</button>
          </form>
          <?php else: ?>
          <span style="color:#7a8494;">Empty</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
