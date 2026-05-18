<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$resortid = $_GET['resortid'] ?? 0;

$stmt = $db->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ? ORDER BY amenity_name");
$stmt->execute([$resortid]);
$amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($amenities);
?>
