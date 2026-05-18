<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$resortid = (int)$_GET['resortid'];
$checkindate = $_GET['checkindate'] ?? '';

$sql = $db->prepare("SELECT * FROM tb_resort_room WHERE resortid = ?" . ($checkindate ? " AND resort_room_id NOT IN (SELECT a.resort_room_id FROM tb_cart a JOIN tb_placed_order b ON a.cart_id = b.cart_id WHERE a.checkindate = ? AND b.reservation_status IN ('Approved', 'PaymentApproval') AND b.cart_id is not null) AND resort_room_id NOT IN (SELECT a.resort_room_id FROM tb_add_on_details a JOIN tb_placed_order b ON a.po_id = b.po_id JOIN tb_cart c ON b.cart_id = c.cart_id WHERE c.checkindate = ? AND b.reservation_status IN ('Approved', 'PaymentApproval') AND b.po_id is not null)" : ""));

if($checkindate) {
    $sql->execute([$resortid, $checkindate, $checkindate]);
} else {
    $sql->execute([$resortid]);
}

$result = $sql->fetchAll(PDO::FETCH_ASSOC);

// Attach room images
$roomIds = array_column($result, 'resort_room_id');
$imageMap = [];
if(!empty($roomIds)) {
    $placeholders = implode(',', array_fill(0, count($roomIds), '?'));
    $imgSql = $db->prepare("SELECT resort_room_id, file_name FROM images WHERE resort_room_id IN ($placeholders)");
    $imgSql->execute($roomIds);
    while($row = $imgSql->fetch(PDO::FETCH_ASSOC)) {
        $imageMap[$row['resort_room_id']][] = $row['file_name'];
    }
}

foreach($result as &$room) {
    $room['images'] = $imageMap[$room['resort_room_id']] ?? [];
}
unset($room);

echo json_encode(['data' => $result]);