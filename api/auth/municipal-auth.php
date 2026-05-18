<?php
session_start();
require_once '../../connection.php';

header('Content-Type: application/json');

try {
    $conn->query("ALTER TABLE tb_municipality ADD COLUMN IF NOT EXISTS last_activity DATETIME DEFAULT NULL");
} catch (Exception $e) {}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM tb_municipality WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user && $password == $user['password']) {
        $_SESSION['type_of_user'] = 'Municipal';
        $_SESSION['user_reg'] = $user['id'];
        
        $update = $conn->prepare("UPDATE tb_municipality SET last_activity = NOW() WHERE id = ?");
        $update->execute([$user['id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
}
?>
