<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$poId = isset($_POST['po_id']) ? $_POST['po_id'] : 0;
$status = isset($_POST['reservation_status']) ? $_POST['reservation_status'] : '';
$resortId = $_SESSION['user_reg'] ?? 0;
$userType = $_SESSION['type_of_user'] ?? '';

$response = [
    'poId' => $poId,
    'status' => $status,
    'resortId' => $resortId,
    'userType' => $userType,
    'sessionSet' => isset($_SESSION['user_reg']),
    'userTypeIsResort' => $userType === 'Resort',
    'post' => $_POST
];

echo json_encode($response);
exit;
?>