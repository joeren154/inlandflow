<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$resortid = $_POST['resortid'] ?? 0;
$room_name = $_POST['room_name'] ?? '';
$room_description = $_POST['room_description'] ?? '';
$room_price = $_POST['room_price'] ?? 0;
$room_capacity = $_POST['room_capacity'] ?? 0;

if (!$room_name || !$room_capacity) {
    echo json_encode(['status' => 'error', 'message' => 'Room name and capacity are required']);
    exit;
}

$sqladdroom = "INSERT INTO tb_resort_room(room_name, room_description, room_capacity, room_price, resortid)VALUES(?, ?, ?, ?, ?)";
$statement = $db->prepare($sqladdroom);
$statement->execute([$room_name, $room_description, $room_capacity, $room_price, $resortid]);

echo json_encode(['status' => 'success', 'message' => 'Room added successfully!']);
