<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Billing.php';

$billing = new Billing();
$bills = $billing->getAllBills();
print_r($bills);
