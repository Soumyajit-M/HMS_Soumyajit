<?php
// Central bootstrap for API endpoints
// Starts session, sets JSON response header and loads common config and DB
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Prevent authentication popups and caching
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header_remove('WWW-Authenticate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

function api_get_json() {
    return json_decode(file_get_contents('php://input'), true);
}

?>
