<?php
require_once '../config/database.php';

$iddelete = $_POST['location_id'];

$GetResortID = $db->prepare("SELECT * FROM tb_location WHERE location_id = ?");
$GetResortID->execute([$iddelete]);
$rowResort = $GetResortID->fetch(PDO::FETCH_ASSOC);

$resortid = $rowResort['resortid'];
$isLocated = 0;

$deleteLocation = $db->prepare("DELETE FROM tb_location WHERE location_id = ?");
$deleteLocation->execute([$iddelete]);

$updateloc = $db->prepare("UPDATE tb_resort SET isLocated = ? WHERE resortid = ?");
$updateloc->execute([$isLocated, $resortid]);

echo "OK";