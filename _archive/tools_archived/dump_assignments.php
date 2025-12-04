<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Room.php';

$room = new Room();
$assignments = $room->getAllBedAssignments();
print_r($assignments);
