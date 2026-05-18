<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$amenity_id = $_POST['amenity_id'] ?? 0;
$amenity_name = $_POST['amenity_name'] ?? '';
$amenity_price = $_POST['amenity_price'] ?? 0;

if (!$amenity_id || !$amenity_name) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid amenity data']);
    exit;
}

$updateAmenity = $db->prepare("UPDATE tb_resort_amenities SET amenity_name = ?, amenity_price = ? WHERE amenity_id = ?");
$updateAmenity->execute([$amenity_name, $amenity_price, $amenity_id]);

echo json_encode(['status' => 'success', 'message' => 'Amenity updated successfully!']);
