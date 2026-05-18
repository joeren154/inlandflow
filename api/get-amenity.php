<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$resortid = $_GET['resortid'];

$sql = $db->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = '$resortid'"); 
$sql->execute();

$result = $sql->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['data' => $result]);