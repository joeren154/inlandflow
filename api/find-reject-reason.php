<?php
require_once '../config/database.php';

$response = array();

$stmt = $db->prepare("SELECT * FROM tb_placed_order WHERE po_id = ?");
$stmt->execute([$_POST['po_id']]);

while ($row = $stmt->fetch()) {
    $response = $row;
}

echo html_entity_decode(htmlentities(json_encode($response)));