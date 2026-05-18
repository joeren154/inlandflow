<?php
require_once '../config/database.php';

$response = array();

$stmt = $db->prepare("SELECT * FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE a.id = ?");
$stmt->execute([$_POST['id']]);

while ($row = $stmt->fetch()) {
    $response = $row;
}

echo html_entity_decode(htmlentities(json_encode($response)));