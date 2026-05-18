<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$resortid = $_POST['resortid'] ?? 0;
$amenity_name = $_POST['amenity_name'] ?? '';
$amenity_price = $_POST['amenity_price'] ?? 0;

if (!$amenity_name) {
    echo json_encode(['status' => 'error', 'message' => 'Amenity name is required']);
    exit;
}

$sqladdamenities = "INSERT INTO tb_resort_amenities(resortid, amenity_name, amenity_price)VALUES(?, ?, ?)";
$statement = $db->prepare($sqladdamenities);
$statement->execute([$resortid, $amenity_name, $amenity_price]);

echo json_encode(['status' => 'success', 'message' => 'Amenity added successfully!']);
