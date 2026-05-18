<?php
require_once '../config/database.php';

$updateAfee = $db->prepare("UPDATE tb_resort SET adultEntranceFee = ? WHERE resortid = ?");
$updateAfee->execute([$_POST['adultEntranceFee'], $_POST['resortid']]);

echo "OK";