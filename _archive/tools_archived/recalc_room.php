<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Room.php';

class RoomDebug extends Room {
    public function recalc($roomId) {
        $reflection = new ReflectionClass(Room::class);
        $method = $reflection->getMethod('updateRoomOccupancy');
        $method->setAccessible(true);
        return $method->invoke($this, $roomId);
    }
}

$room = new RoomDebug();

$database = new Database();
$conn = $database->getConnection();
$stmt = $conn->query('SELECT id FROM rooms');
$roomIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($roomIds as $roomId) {
    $room->recalc($roomId);
    echo "Recalculated room ID: {$roomId}\n";
}

echo "Done\n";
