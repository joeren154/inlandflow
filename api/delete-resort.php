<?php
require_once '../config/database.php';

$iddelete = $_POST['resortid'];

$deleteResort = $db->prepare("DELETE FROM tb_resort WHERE resortid = ?");
$deleteResort->execute([$iddelete]);

echo "OK";