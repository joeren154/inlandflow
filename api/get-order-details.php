<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$po_id = $_GET['po_id'] ?? 0;

$stmt = $db->prepare("
    SELECT po.*, c.resortid  
    FROM tb_placed_order po
    JOIN tb_cart c ON po.cart_id = c.cart_id
    WHERE po.po_id = ?
");
$stmt->execute([$po_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data ?: []);
?>
