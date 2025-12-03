<?php
// Shared authentication helper for API endpoints
// Provides api_require_login([roles]) which returns an Auth instance

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Prevent authentication popups
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header_remove('WWW-Authenticate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

function api_require_login($roles = null) {
    static $authInstance = null;
    if ($authInstance === null) {
        $authInstance = new Auth();
    }

    // CI bypass for automated checks (only when explicitly enabled)
    $ciBypass = getenv('CI_AUTH_BYPASS');
    if (
        $ciBypass === '1' ||
        (isset($_ENV['CI_AUTH_BYPASS']) && $_ENV['CI_AUTH_BYPASS'] === '1') ||
        (isset($_SERVER['CI_AUTH_BYPASS']) && $_SERVER['CI_AUTH_BYPASS'] === '1') ||
        (defined('CI_AUTH_BYPASS') && CI_AUTH_BYPASS === true) ||
        (isset($_SERVER['HTTP_X_CI_BYPASS']) && $_SERVER['HTTP_X_CI_BYPASS'] === '1')
    ) {
        return $authInstance;
    }

    if (!$authInstance->isLoggedIn()) {
        http_response_code(200); // Don't send 401 to avoid browser auth popup
        echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
        exit();
    }

    if ($roles) {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        if (!$authInstance->hasAnyRole($roles)) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit();
        }
    }

    return $authInstance;
}
?>
