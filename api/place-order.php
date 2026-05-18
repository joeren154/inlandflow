<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$cart_id = $_POST['cart_id'] ?? 0;
$adult_fee = (float)($_POST['adult_fee'] ?? 0);
$kids_fee = (float)($_POST['kids_fee'] ?? 0);
$payment_method = $_POST['payment_method'] ?? 'Cash on Arrival';
$message = $_POST['message'] ?? '';

if (!$cart_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing cart_id']);
    exit;
}

try {
    $cartStmt = $db->prepare("
        SELECT c.*, r.adultEntranceFee, r.kidsEntranceFee, rr.room_price 
        FROM tb_cart c 
        JOIN tb_resort r ON c.resortid = r.resortid 
        LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id 
        WHERE c.cart_id = ?
    ");
    $cartStmt->execute([$cart_id]);
    $cart = $cartStmt->fetch();
    
    if (!$cart) {
        echo json_encode(['status' => 'error', 'message' => 'Cart not found']);
        exit;
    }
    
    $checkin = new DateTime($cart['checkindate']);
    $checkout = new DateTime($cart['checkoutdate']);
    $numDays = max(1, $checkout->diff($checkin)->days);
    
    $room_price = (float)($cart['room_price'] ?? 0);
    $room_fee = $room_price * $numDays;
    
    $total_fee = $adult_fee + $kids_fee + $room_fee;
    
    $maxOrderId = $db->query("SELECT MAX(po_id) as max_id FROM tb_placed_order")->fetch();
    $newOrderId = ($maxOrderId['max_id'] ?? 0) + 1;
    
    $columns = "po_id, cart_id, adult_fee, kids_fee, total_fee, payment_method, message, reservation_status";
    $values = "?, ?, ?, ?, ?, ?, ?, 'Pending'";
    $params = [$newOrderId, $cart_id, $adult_fee, $kids_fee, $total_fee, $payment_method, $message];
    
    try {
        $db->exec("SELECT room_fee FROM tb_placed_order LIMIT 0");
        $columns = "po_id, cart_id, adult_fee, kids_fee, room_fee, num_days, total_fee, payment_method, message, reservation_status";
        $values = "?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending'";
        $params = [$newOrderId, $cart_id, $adult_fee, $kids_fee, $room_fee, $numDays, $total_fee, $payment_method, $message];
    } catch (Exception $e) {}
    
    $sqlplaceorder = "INSERT INTO tb_placed_order($columns) VALUES($values)";
    $statement = $db->prepare($sqlplaceorder);
    $statement->execute($params);

    $cart_status = "Place Order";
    $updateCart = $db->prepare("UPDATE tb_cart SET cart_status = ? WHERE cart_id = ?");
    $updateCart->execute([$cart_status, $cart_id]);

    echo json_encode(['status' => 'success', 'message' => 'Order placed']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>