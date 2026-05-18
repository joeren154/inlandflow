<?php
session_start();
require_once '../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $roomId = $_GET['id'];
    $resortId = $_SESSION['user_reg'];
    
    $stmt = $conn->prepare("SELECT * FROM tb_resort_room WHERE resort_room_id = ? AND resortid = ?");
    $stmt->execute([$roomId, $resortId]);
    $room = $stmt->fetch();
    
    echo json_encode($room);
}
?>