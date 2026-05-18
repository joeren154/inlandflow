<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$resortmun = $db->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$resortmun->execute([$_SESSION['user_reg']]);
$rowmunicipality = $db->fetch(PDO::FETCH_ASSOC);

$r_municipality = $rowmunicipality['mun'];

$sql = $db->prepare("SELECT * FROM tb_resort WHERE mun = ? AND isLocated = 0"); 
$sql->execute([$r_municipality]);

$result = $sql->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['data' => $result]);