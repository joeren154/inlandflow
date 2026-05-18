<?php
session_start();
require_once '../../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM tb_guest WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user && $password == $user['Password']) {
        $_SESSION['type_of_user'] = 'Guest';
        $_SESSION['user_reg'] = $user['guest_id'];
        
        if(isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
            foreach($_SESSION['guest_cart'] as $cartItem) {
                $resortid = (int)$cartItem['resortid'];
                $resort_room_id = !empty($cartItem['resort_room_id']) ? (int)$cartItem['resort_room_id'] : 0;
                $checkindate = $cartItem['checkindate'];
                $checkoutdate = $cartItem['checkoutdate'];
                $num_adults = (int)$cartItem['num_adults'];
                $num_kids = (int)$cartItem['num_kids'];
                
                $maxId = $conn->query("SELECT MAX(cart_id) as max_id FROM tb_cart")->fetch();
                $newCartId = ($maxId['max_id'] ?? 0) + 1;
                
                $cart = $conn->prepare("INSERT INTO tb_cart (cart_id, guest_id, resortid, resort_room_id, checkindate, checkoutdate, num_adults, num_kids, cart_status) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
                $cart->execute([$newCartId, $user['guest_id'], $resortid, $resort_room_id, $checkindate, $checkoutdate, $num_adults, $num_kids]);
            }
            unset($_SESSION['guest_cart']);
        }
        
        // Update status
        $update = $conn->prepare("UPDATE tb_guest SET status = 'Online now' WHERE guest_id = ?");
        $update->execute([$user['guest_id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
}
?>