<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$amenity_id = $_POST['amenity_id'] ?? 0;

if (!$amenity_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid amenity ID']);
    exit;
}

$deleteAmenity = $db->prepare("DELETE FROM tb_resort_amenities WHERE amenity_id = ?");
$deleteAmenity->execute([$amenity_id]);

echo json_encode(['status' => 'success', 'message' => 'Amenity deleted successfully!']);
