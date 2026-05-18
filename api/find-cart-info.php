<?php
require_once '../config/database.php';

$response = array();

$stmt = $db->prepare("SELECT * FROM tb_cart a JOIN tb_resort b on a.resortid = b.resortid JOIN tb_resort_room c on a.resort_room_id = c.resort_room_id WHERE a.cart_id = ?");
$stmt->execute([$_POST['cart_id']]);

while ($row = $stmt->fetch()) {
    $response = $row;
}

echo html_entity_decode(htmlentities(json_encode($response)));