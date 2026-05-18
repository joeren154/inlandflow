<?php
require_once '../config/database.php';

$iddelete = $_POST['add_on_details_id'];

// Get po_id before deleting
$getPoId = $db->prepare("SELECT po_id FROM tb_add_on_details WHERE add_on_details_id = ?");
$getPoId->execute([$iddelete]);
$po_id = $getPoId->fetchColumn();

$deleteDetails = $db->prepare("DELETE FROM tb_add_on_details WHERE add_on_details_id = ?");
$deleteDetails->execute([$iddelete]);

// Recalculate total after deleting
if ($po_id) {
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
}

echo "OK|" . number_format($newTotal, 2);