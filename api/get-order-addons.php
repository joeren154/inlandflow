<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$po_id = $_GET['po_id'] ?? 0;

// Get add-on amenities
$amenities = $db->prepare("
    SELECT aoa.*, ra.amenity_name, ra.amenity_price
    FROM tb_add_on_amenities aoa
    JOIN tb_resort_amenities ra ON aoa.amenity_id = ra.amenity_id
    WHERE aoa.po_id = ?
");
$amenities->execute([$po_id]);
$amenitiesList = $amenities->fetchAll(PDO::FETCH_ASSOC);

// Get add-on rooms
$rooms = $db->prepare("
    SELECT aod.*, rr.room_name, rr.room_price
    FROM tb_add_on_details aod
    JOIN tb_resort_room rr ON aod.resort_room_id = rr.resort_room_id
    WHERE aod.po_id = ?
");
$rooms->execute([$po_id]);
$roomsList = $rooms->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'amenities' => $amenitiesList,
    'rooms' => $roomsList
]);
?>
