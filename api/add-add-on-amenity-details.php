<?php
require_once '../config/database.php';

$po_id = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
$amenity_id = isset($_POST['amenity_id']) ? (int)$_POST['amenity_id'] : 0;
$amenity_price = isset($_POST['amenity_price']) ? (float)$_POST['amenity_price'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if (!$po_id || !$amenity_id) {
    echo "Error: Missing required fields";
    exit;
}

if ($amenity_price <= 0) {
    echo "Error: Invalid price";
    exit;
}

$total_amenity_fee = $amenity_price * $quantity;

// Get next ID manually since AUTO_INCREMENT might not be set
$maxIdStmt = $db->query("SELECT COALESCE(MAX(add_on_amenity_id), 0) + 1 FROM tb_add_on_amenities");
$nextId = $maxIdStmt->fetchColumn();

$sqladdonamenity = "INSERT INTO tb_add_on_amenities(add_on_amenity_id, po_id, amenity_id, quantity, total_amenity_fee) VALUES(:id, :po_id, :amenity_id, :quantity, :total_fee)";
$statement = $db->prepare($sqladdonamenity);
$statement->execute([
    ':id' => $nextId,
    ':po_id' => $po_id,
    ':amenity_id' => $amenity_id,
    ':quantity' => $quantity,
    ':total_fee' => $total_amenity_fee
]);

// Recalculate and update total_fee in tb_placed_order
$calc = $db->prepare("
    SELECT
        po.adult_fee,
        po.kids_fee,
        c.resort_room_id,
        rr.room_price,
        c.checkindate,
        c.checkoutdate,
        IFNULL((SELECT SUM(total_fee) FROM tb_add_on_details WHERE po_id = ?), 0) as addon_rooms_total,
        IFNULL((SELECT SUM(total_amenity_fee) FROM tb_add_on_amenities WHERE po_id = ?), 0) as addon_amenities_total
    FROM tb_placed_order po
    JOIN tb_cart c ON po.cart_id = c.cart_id
    LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
    WHERE po.po_id = ?
");
$calc->execute([$po_id, $po_id, $po_id]);
$data = $calc->fetch();

$checkin_dt = new DateTime($data['checkindate']);
$checkout_dt = new DateTime($data['checkoutdate']);
$numDays = max(1, $checkout_dt->diff($checkin_dt)->days);

$room_total = ($data['room_price'] ?? 0) * $numDays;
$newTotal = $data['adult_fee'] + $data['kids_fee'] + $room_total + $data['addon_rooms_total'] + $data['addon_amenities_total'];

try {
    $db->prepare("UPDATE tb_placed_order SET total_fee = ?, num_days = ? WHERE po_id = ?")->execute([$newTotal, $numDays, $po_id]);
} catch (Exception $e) {
    $db->prepare("UPDATE tb_placed_order SET total_fee = ? WHERE po_id = ?")->execute([$newTotal, $po_id]);
}

echo "OK|" . number_format($newTotal, 2);
?>