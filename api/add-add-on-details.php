<?php
require_once '../config/database.php';

$po_id = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
$resortid = isset($_POST['resortid']) ? (int)$_POST['resortid'] : 0;
$resort_room_id = isset($_POST['resort_room_id']) ? (int)$_POST['resort_room_id'] : 0;
$num_adults = isset($_POST['num_adults']) ? (int)$_POST['num_adults'] : 1;
$num_kids = isset($_POST['num_kids']) ? (int)$_POST['num_kids'] : 0;

if (!$po_id || !$resort_room_id) {
    echo "Error: Missing required fields";
    exit;
}

$entranceresort = $db->prepare("SELECT * FROM tb_resort a JOIN tb_resort_room b ON a.resortid = b.resortid WHERE b.resort_room_id = ?");
$entranceresort->execute([$resort_room_id]);
$rowentrance = $entranceresort->fetch(PDO::FETCH_ASSOC);

if (!$rowentrance) {
    echo "Error: Room not found";
    exit;
}

$checkinStmt = $db->prepare("SELECT c.checkindate, c.checkoutdate FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE po.po_id = ?");
$checkinStmt->execute([$po_id]);
$dateRow = $checkinStmt->fetch();

$checkin = new DateTime($dateRow['checkindate']);
$checkout = new DateTime($dateRow['checkoutdate']);
$numDays = max(1, $checkout->diff($checkin)->days);

$room_total_fee = $rowentrance['room_price'] * $numDays;
$adult_fee = $rowentrance['adultEntranceFee'] * $num_adults;
$kids_fee = $rowentrance['kidsEntranceFee'] * $num_kids;
$total_fee = $adult_fee + $kids_fee + $room_total_fee;

$countexist = $db->prepare("SELECT COUNT(add_on_details_id) FROM tb_add_on_details WHERE resort_room_id = ? AND po_id = ?");
$countexist->execute([$resort_room_id, $po_id]);
$count = $countexist->fetchColumn();

if($count == 0) {
    $sqladdons = "INSERT INTO tb_add_on_details(po_id, resort_room_id, num_adults, adult_fee, num_kids, kids_fee, total_fee) VALUES(:po_id, :room_id, :adults, :adult_fee, :kids, :kids_fee, :total_fee)";
    $statement = $db->prepare($sqladdons);
    $statement->execute([
        ':po_id' => $po_id,
        ':room_id' => $resort_room_id,
        ':adults' => $num_adults,
        ':adult_fee' => $adult_fee,
        ':kids' => $num_kids,
        ':kids_fee' => $kids_fee,
        ':total_fee' => $total_fee
    ]);

    $calc = $db->prepare("
        SELECT
            po.adult_fee,
            po.kids_fee,
            po.room_fee,
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
    
    $main_room_total = ($data['room_price'] ?? 0) * $numDaysCalc;
    $newTotal = $data['adult_fee'] + $data['kids_fee'] + $main_room_total + $data['addon_rooms_total'] + $data['addon_amenities_total'];
    
    try {
        $db->prepare("UPDATE tb_placed_order SET total_fee = ?, num_days = ? WHERE po_id = ?")->execute([$newTotal, $numDaysCalc, $po_id]);
    } catch (Exception $e) {
        $db->prepare("UPDATE tb_placed_order SET total_fee = ? WHERE po_id = ?")->execute([$newTotal, $po_id]);
    }

    echo "OK|" . number_format($newTotal, 2);
} else {
    echo "Room already Added";
}
?>