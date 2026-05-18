<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$po_id = $_GET['po_id'] ?? 0;

$calc = $db->prepare("
    SELECT
        po.adult_fee,
        po.kids_fee,
        c.resort_room_id,
        c.checkindate,
        c.checkoutdate,
        IFNULL(rr.room_price, 0) as room_price,
        IFNULL((SELECT SUM(total_fee) FROM tb_add_on_details WHERE po_id = ?), 0) as addon_rooms_total,
        IFNULL((SELECT SUM(total_amenity_fee) FROM tb_add_on_amenities WHERE po_id = ?), 0) as addon_amenities_total
    FROM tb_placed_order po
    JOIN tb_cart c ON po.cart_id = c.cart_id
    LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
    WHERE po.po_id = ?
");
$calc->execute([$po_id, $po_id, $po_id]);
$data = $calc->fetch(PDO::FETCH_ASSOC);

$checkin = new DateTime($data['checkindate']);
$checkout = new DateTime($data['checkoutdate']);
$numDays = max(1, $checkout->diff($checkin)->days);

$room_total = ($data['room_price'] ?? 0) * $numDays;
$total = ($data['adult_fee'] ?? 0) + ($data['kids_fee'] ?? 0) + $room_total + ($data['addon_rooms_total'] ?? 0) + ($data['addon_amenities_total'] ?? 0);

echo json_encode(['total' => $total, 'num_days' => $numDays]);
?>
