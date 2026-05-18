<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../connection.php';

header('Content-Type: application/json');

$response = ['exists' => false];

if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['username'])) {
    $username = $_POST['username'];
    
    $stmt = $conn->prepare("SELECT guest_id FROM tb_guest WHERE Username = ?");
    $stmt->execute([$username]);
    
    $response['exists'] = $stmt->rowCount() > 0;
}

echo json_encode($response);
exit;
?>
