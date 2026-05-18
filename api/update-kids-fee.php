<?php
require_once '../config/database.php';

$updateRoom = $db->prepare("UPDATE tb_resort SET kidsEntranceFee = ? WHERE resortid = ?");
$updateRoom->execute([$_POST['kidsEntranceFee'], $_POST['resortid']]);

echo "OK";