<?php
require_once '../config/database.php';

$entranceresort = $db->prepare("SELECT * FROM tb_resort a JOIN tb_resort_room b ON a.resortid = b.resortid WHERE b.resort_room_id = ?");
$entranceresort->execute([$_POST['resort_room_id']]);
$rowentrance = $entranceresort->fetch(PDO::FETCH_ASSOC);

$checkinStmt = $db->prepare("SELECT c.checkindate, c.checkoutdate FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE po.po_id = (SELECT po_id FROM tb_add_on_details WHERE add_on_details_id = ?)");
$checkinStmt->execute([$_POST['add_on_details_id']]);
$dateRow = $checkinStmt->fetch();

$checkin = new DateTime($dateRow['checkindate']);
$checkout = new DateTime($dateRow['checkoutdate']);
$numDays = max(1, $checkout->diff($checkin)->days);

$num_kids = $_POST['num_kids'];
$num_adults = $_POST['num_adults'];
$adult_fee = $rowentrance['adultEntranceFee'] * $num_adults;
$kids_fee = $rowentrance['kidsEntranceFee'] * $num_kids;
$EntranceFee = $adult_fee + $kids_fee;
$total_fee = $EntranceFee + ($rowentrance['room_price'] * $numDays);

$updateDetails = $db->prepare("UPDATE tb_add_on_details SET resort_room_id = ?, num_kids = ?, kids_fee = ?, num_adults = ?, adult_fee = ?, total_fee = ? WHERE add_on_details_id = ?");
$updateDetails->execute([$_POST['resort_room_id'], $num_kids, $kids_fee, $num_adults, $adult_fee, $total_fee, $_POST['add_on_details_id']]);

// Get po_id and recalculate total
$getPoId = $db->prepare("SELECT po_id FROM tb_add_on_details WHERE add_on_details_id = ?");
$getPoId->execute([$_POST['add_on_details_id']]);
$po_id = $getPoId->fetchColumn();

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
    $numDaysCalc = max(1, $checkout_dt->diff($checkin_dt)->days);
    
    $room_total = ($data['room_price'] ?? 0) * $numDaysCalc;
    $newTotal = $data['adult_fee'] + $data['kids_fee'] + $room_total + $data['addon_rooms_total'] + $data['addon_amenities_total'];
    
    try {
        $db->prepare("UPDATE tb_placed_order SET total_fee = ?, num_days = ? WHERE po_id = ?")->execute([$newTotal, $numDaysCalc, $po_id]);
    } catch (Exception $e) {
        $db->prepare("UPDATE tb_placed_order SET total_fee = ? WHERE po_id = ?")->execute([$newTotal, $po_id]);
    }
}

echo "OK|" . number_format($newTotal, 2);