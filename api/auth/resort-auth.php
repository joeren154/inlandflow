<?php
session_start();
require_once '../../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM tb_resort WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user && $password == $user['password']) {
        $_SESSION['type_of_user'] = 'Resort';
        $_SESSION['user_reg'] = $user['resortid'];
        
        $conn->query("ALTER TABLE tb_resort ADD COLUMN IF NOT EXISTS last_activity DATETIME DEFAULT NULL");
        $update = $conn->prepare("UPDATE tb_resort SET status = 'Online now', last_activity = NOW() WHERE resortid = ?");
        $update->execute([$user['resortid']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
}
?>
