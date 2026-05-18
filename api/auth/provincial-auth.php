<?php
session_start();
require_once '../../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM tb_provincial WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user && $password == $user['password']) {
        $_SESSION['type_of_user'] = 'Provincial';
        $_SESSION['user_reg'] = $user['provid'];
        
        // Update status
        $update = $conn->prepare("UPDATE tb_provincial SET status = 'Online now' WHERE provid = ?");
        $update->execute([$user['provid']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
}
?>