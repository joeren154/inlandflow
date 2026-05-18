<?php
require_once '../config/database.php';

$response = array();

$stmt = $db->prepare("SELECT * FROM tb_location a JOIN tb_resort b ON a.resortid = b.resortid WHERE a.location_id = ?");
$stmt->execute([$_POST['location_id']]);

while ($row = $stmt->fetch()) {
    $response = $row;
}

echo html_entity_decode(htmlentities(json_encode($response)));