<?php
require_once '../config/database.php';

$updateReason = $db->prepare("UPDATE tb_placed_order SET reject_reason = ? WHERE po_id = ?");
$updateReason->execute([$_POST['reject_reason'], $_POST['po_id']]);

echo "OK";