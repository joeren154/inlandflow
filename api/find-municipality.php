<?php
require_once '../config/database.php';

$response = array();

$stmt = $db->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$stmt->execute([$_POST['id']]);

while ($row = $stmt->fetch()) {
    $response = $row;
}

echo html_entity_decode(htmlentities(json_encode($response)));