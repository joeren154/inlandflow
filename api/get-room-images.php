<?php
require_once '../config/database.php';

$resortid = $_SESSION['user_reg'] ?? 0;
$resort_room_id = (int)($_GET['resort_room_id'] ?? 0);

if (!$resortid || !$resort_room_id) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("SELECT id, file_name FROM images WHERE resortid = ? AND resort_room_id = ? ORDER BY id DESC");
$stmt->execute([$resortid, $resort_room_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($images);
