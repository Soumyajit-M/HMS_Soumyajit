<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];

require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../classes/Billing.php';
require_once __DIR__ . '/../api/auth_helper.php';

// Mock login bypass
define('CI_AUTH_BYPASS', true);
putenv('CI_AUTH_BYPASS=1');

ob_start();
include __DIR__ . '/../api/billing.php';
$output = ob_get_clean();

echo $output;
