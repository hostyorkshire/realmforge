<?php
/**
 * Admin – Image Generation Info
 *
 * Scene images are now generated entirely in the browser using HTML5 Canvas.
 * There is no server-side image cache to manage.
 */

require_once __DIR__ . '/../../config.php';
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
    .info { background: #161c24; border: 1px solid #2a3347; border-radius: 6px;
            padding: 1.25rem; margin-top: 1.5rem; max-width: 640px; }
    .info p { margin: 0.5rem 0; font-size: 0.95rem; line-height: 1.6; }
    .info code { background: #0b0f14; padding: 0.15rem 0.4rem; border-radius: 3px;
                 font-size: 0.85rem; color: #6ab0f3; }
  </style>
</head>
<body>
  <h1>🖼 Image Generation</h1>
  <nav class="nav"><a href="dashboard.php">← Dashboard</a></nav>

  <div class="info">
    <p>
      Scene illustrations are now generated <strong>entirely in the browser</strong>
      using <strong>HTML5 Canvas</strong>. There is no server-side image generation,
      no Stable Diffusion API, and no image cache to manage.
    </p>
    <p>
      Each time a player takes an action the JavaScript function
      <code>drawSceneCanvas()</code> in <code>public/app.js</code>
      draws a procedural scene illustration directly into the
      <code>&lt;canvas id="sceneCanvas"&gt;</code> element, driven by the
      current location and action.
    </p>
    <p>
      Scenes are deterministic for the same location/action pair, so the
      illustration is consistent for the same game context.
      No disk space, write permissions, or external API keys are required.
    </p>
  </div>
</body>
</html>
