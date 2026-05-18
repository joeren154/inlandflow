<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_reg']) || !isset($_SESSION['type_of_user']) || $_SESSION['type_of_user'] !== 'Guest') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$cartId = $_POST['cart_id'] ?? 0;
$guestId = $_SESSION['user_reg'];

if (!$cartId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart ID']);
    exit;
}

try {
    $delete = $db->prepare("DELETE FROM tb_cart WHERE cart_id = ? AND guest_id = ? AND cart_status = 'Pending'");
    $delete->execute([$cartId, $guestId]);
    
    $deleteOrder = $db->prepare("DELETE FROM tb_placed_order WHERE cart_id = ?");
    $deleteOrder->execute([$cartId]);
    
    echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>