<?php
session_start();
require_once '../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $resortId = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT resortid, resortname, isFeatured, isTopItem, isBestSeller, isPromoDeals, isOnSale FROM tb_resort WHERE resortid = ?");
    $stmt->execute([$resortId]);
    $resort = $stmt->fetch();
    
    echo json_encode($resort);
}
?>