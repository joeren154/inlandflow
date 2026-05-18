<?php
require_once '../config/database.php';

$updateMes = $db->prepare("UPDATE tb_placed_order SET message = ? WHERE po_id = ?");
$updateMes->execute([$_POST['message'], $_POST['po_id']]);

echo "OK";