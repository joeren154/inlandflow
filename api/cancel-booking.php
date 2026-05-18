<?php
session_start();
require_once '../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cartId = $_POST['cart_id'];
    $guestId = $_SESSION['user_reg'];
    
    // Verify booking belongs to guest
    $check = $conn->prepare("SELECT cart_id FROM tb_cart WHERE cart_id = ? AND guest_id = ?");
    $check->execute([$cartId, $guestId]);
    
    if($check->rowCount() > 0) {
        $update = $conn->prepare("UPDATE tb_cart SET cart_status = 'Cancelled' WHERE cart_id = ?");
        $update->execute([$cartId]);
        
        $updateOrder = $conn->prepare("UPDATE tb_placed_order SET reservation_status = 'Cancelled' WHERE cart_id = ?");
        $updateOrder->execute([$cartId]);
        
        echo json_encode(['status' => 'success', 'message' => 'Booking cancelled successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
    }
}
?>