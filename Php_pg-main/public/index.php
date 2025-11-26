<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');   // don’t show warnings in browser
ini_set('log_errors', '1');       // still log them to error_log
ob_start();                       // buffer output to protect headers


require_once __DIR__ . '/../src/Controller.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/auth.php';
require_login();
// Read from either GET or POST so forms work.
$action = $_REQUEST['action'] ?? 'list';
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;

// New User Management Routes
if ($action === 'user_new') {
    route_user_new_form();
} elseif ($action === 'user_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    route_user_create();
}
// End New User Management Routes

// Proposal Management Routes
elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    route_create();
} elseif ($action === 'new') {
    route_new_form();
} elseif ($action === 'show' && $id) {
    route_show($id);
} elseif ($action === 'download' && $id) {
    route_download_pdf($id);
} elseif ($action === 'edit' && $id) {
    route_edit_form($id);
} elseif ($action === 'update' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    route_update($id);
} elseif ($action === 'delete' && $id) {
    route_delete($id);
} else {
    route_list();
}
?>
