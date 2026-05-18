<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$sql = $db->prepare("SELECT * FROM tb_resort WHERE resortid = ?"); 
$sql->execute([$_SESSION['user_reg']]);

$result = $sql->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['data' => $result]);