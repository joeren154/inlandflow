<?php
require_once '../config/database.php';

$response = array();

$stmt = $db->prepare("SELECT * FROM tb_resort_room WHERE resort_room_id = ?");
$stmt->execute([$_POST['resort_room_id']]);

while ($row = $stmt->fetch()) {
    $response = $row;
}

echo html_entity_decode(htmlentities(json_encode($response)));