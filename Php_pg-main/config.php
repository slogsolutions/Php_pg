<?php
// ====== DB ======
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'proposal_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
// If your MySQL runs on a non-default port, add this:
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// ====== Paths ======
define('BASE_PATH', __DIR__);              // ✅ project root
define('PUBLIC_URL_PATH', '/');            // adjust if app is in a subfolder

define('STORAGE_PATH', BASE_PATH . '/storage/proposals');
if (!is_dir(STORAGE_PATH)) { @mkdir(STORAGE_PATH, 0775, true); }

// ====== Errors ======
error_reporting(E_ALL);
ini_set('display_errors', 1);
