<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../connection.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['reg_username'] ?? '';
    $password = $_POST['reg_password'] ?? '';
    
    if(empty($firstname) || empty($lastname) || empty($username) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'All fields are required'];
        echo json_encode($response);
        exit;
    }
    
    $check = $conn->prepare("SELECT guest_id FROM tb_guest WHERE Username = ?");
    $check->execute([$username]);
    
    if($check->rowCount() > 0) {
        $response = ['status' => 'error', 'message' => 'Username already exists'];
        echo json_encode($response);
        exit;
    }
    
    $insert = $conn->prepare("INSERT INTO tb_guest (LastName, FirstName, MiddleName, Address, ContactNo, Username, Password, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'Offline now')");
    
    if($insert->execute([$lastname, $firstname, $middlename, $address, $phone, $username, $password])) {
        $newGuestId = $conn->lastInsertId();
        $response = ['status' => 'success', 'message' => 'Registration successful'];
        
        $_SESSION['type_of_user'] = 'Guest';
        $_SESSION['user_reg'] = $newGuestId;
        
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
                $cart->execute([$newCartId, $newGuestId, $resortid, $resort_room_id, $checkindate, $checkoutdate, $num_adults, $num_kids]);
            }
            unset($_SESSION['guest_cart']);
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Registration failed. Please try again.'];
    }
}

echo json_encode($response);
exit;
?>