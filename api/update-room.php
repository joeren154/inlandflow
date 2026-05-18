<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$room_id = $_POST['room_id'] ?? 0;
$resortid = $_POST['resortid'] ?? 0;
$room_name = $_POST['room_name'] ?? '';
$room_description = $_POST['room_description'] ?? '';
$room_capacity = $_POST['room_capacity'] ?? 0;
$room_price = $_POST['room_price'] ?? 0;
$room_status = $_POST['room_status'] ?? 'Available';

if (!$room_id || !$room_name || !$room_capacity) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid room data']);
    exit;
}

$update = $db->prepare("UPDATE tb_resort_room SET room_name = ?, room_description = ?, room_capacity = ?, room_price = ?, room_status = ? WHERE resort_room_id = ? AND resortid = ?");
$update->execute([$room_name, $room_description, $room_capacity, $room_price, $room_status, $room_id, $resortid]);

echo json_encode(['status' => 'success', 'message' => 'Room updated successfully!']);
?>
