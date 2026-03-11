<?php
/**
 * RealmForge Configuration
 * 
 * Copy this file and fill in your API credentials.
 * Never expose these values to the frontend.
 */

// Groq API
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: 'your-groq-api-key-here');
define('GROQ_ENDPOINT', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL', 'llama-3.1-8b-instant');

// Stable Diffusion API
define('STABLE_DIFFUSION_API_KEY', getenv('STABLE_DIFFUSION_API_KEY') ?: 'your-stable-diffusion-api-key-here');
define('STABLE_DIFFUSION_ENDPOINT', 'https://api.stability.ai/v2beta/stable-image/generate/core');

// Paths
// BASE_PATH   = /home/playrealm                  (repo root, one level above public_html)
// PUBLIC_PATH = /home/playrealm/public_html      (the web root)
define('BASE_PATH',    dirname(__FILE__));
define('PUBLIC_PATH',  BASE_PATH . '/public_html');
define('IMAGES_PATH',  PUBLIC_PATH . '/images/generated');  // inside public_html – web-accessible
define('LOGS_PATH',    BASE_PATH  . '/logs');               // above public_html – not web-accessible
define('DATABASE_PATH', BASE_PATH . '/database');           // above public_html – not web-accessible

// World settings
define('WORLD_FILE', DATABASE_PATH . '/world.json');
define('WORLD_GRID_SIZE', 50);

// Story settings
define('STORY_MIN_WORDS', 80);
define('STORY_MAX_WORDS', 120);
define('MAX_HISTORY_EVENTS', 5);

// Admin credentials (change these before deployment)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'changeme');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/errors.log');
