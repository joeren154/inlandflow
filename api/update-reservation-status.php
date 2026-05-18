<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$poId = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
$status = isset($_POST['reservation_status']) ? $_POST['reservation_status'] : '';
$resortId = $_SESSION['user_reg'] ?? 0;
$userType = $_SESSION['type_of_user'] ?? '';

if (!$poId) {
    echo json_encode(['status' => 'error', 'message' => 'po_id is required']);
    exit;
}

if (!$status) {
    echo json_encode(['status' => 'error', 'message' => 'reservation_status is required']);
    exit;
}

if ($userType !== 'Resort') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$validStatuses = ['Pending', 'PaymentApproval', 'Approved', 'Rejected', 'Completed', 'Reviewed'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status: ' . $status]);
    exit;
}

try {
    // Get reservation info first
    $resInfo = $db->prepare("
        SELECT po.po_id, c.resortid, c.resort_room_id, c.checkindate, c.checkoutdate, c.num_adults, c.num_kids
        FROM tb_placed_order po 
        JOIN tb_cart c ON po.cart_id = c.cart_id 
        WHERE po.po_id = ? AND c.resortid = ?
    ");
    $resInfo->execute([$poId, $resortId]);
    $reservation = $resInfo->fetch();
    
    if (!$reservation) {
        echo json_encode(['status' => 'error', 'message' => 'Reservation not found for this resort']);
        exit;
    }
    
    // Update status
    $update = $db->prepare("UPDATE tb_placed_order SET reservation_status = ? WHERE po_id = ?");
    $update->execute([$status, $poId]);
    
    // Get resort fees
    $resortFees = $db->prepare("SELECT adultEntranceFee, kidsEntranceFee FROM tb_resort WHERE resortid = ?");
    $resortFees->execute([$resortId]);
    $fees = $resortFees->fetch();
    
    // Calculate days
    $checkin = new DateTime($reservation['checkindate']);
    $checkout = new DateTime($reservation['checkoutdate']);
    $numDays = max(1, $checkout->diff($checkin)->days);
    
    // Calculate base fees
    $adult_fee = $reservation['num_adults'] * ($fees['adultEntranceFee'] ?? 0);
    $kids_fee = $reservation['num_kids'] * ($fees['kidsEntranceFee'] ?? 0);
    
    // Get room price
    $room_fee = 0;
    if ($reservation['resort_room_id'] > 0) {
        $roomPrice = $db->prepare("SELECT room_price FROM tb_resort_room WHERE resort_room_id = ?");
        $roomPrice->execute([$reservation['resort_room_id']]);
        $roomData = $roomPrice->fetch();
        if ($roomData) {
            $room_fee = $roomData['room_price'] * $numDays;
        }
    }
    
    // Get add-on rooms total
    $addonRooms = $db->prepare("SELECT COALESCE(SUM(total_fee), 0) as addon_rooms_total FROM tb_add_on_details WHERE po_id = ?");
    $addonRooms->execute([$poId]);
    $addonRoomsTotal = $addonRooms->fetchColumn();
    
    // Get add-on amenities total
    $addonAmenities = $db->prepare("SELECT COALESCE(SUM(total_amenity_fee), 0) as addon_amenities_total FROM tb_add_on_amenities WHERE po_id = ?");
    $addonAmenities->execute([$poId]);
    $addonAmenitiesTotal = $addonAmenities->fetchColumn();
    
    // Calculate grand total
    $newTotal = $adult_fee + $kids_fee + $room_fee + $addonRoomsTotal + $addonAmenitiesTotal;
    
    // Update total_fee
    try {
        $db->prepare("UPDATE tb_placed_order SET total_fee = ?, room_fee = ?, num_days = ? WHERE po_id = ?")
            ->execute([$newTotal, $room_fee, $numDays, $poId]);
    } catch (Exception $e) {
        $db->prepare("UPDATE tb_placed_order SET total_fee = ? WHERE po_id = ?")
            ->execute([$newTotal, $poId]);
    }
    
    // Update room availability
    if ($status === 'PaymentApproval' || $status === 'Approved') {
        if ($reservation['resort_room_id'] > 0) {
            $db->prepare("UPDATE tb_resort_room SET room_status = 'Not Available' WHERE resort_room_id = ?")
                ->execute([$reservation['resort_room_id']]);
        }
    } elseif ($status === 'Rejected' || $status === 'Completed') {
        if ($reservation['resort_room_id'] > 0) {
            $db->prepare("UPDATE tb_resort_room SET room_status = 'Available' WHERE resort_room_id = ?")
                ->execute([$reservation['resort_room_id']]);
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'total_fee' => $newTotal,
        'num_days' => $numDays,
        'adult_fee' => $adult_fee,
        'kids_fee' => $kids_fee,
        'room_fee' => $room_fee,
        'addon_rooms' => $addonRoomsTotal,
        'addon_amenities' => $addonAmenitiesTotal
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>