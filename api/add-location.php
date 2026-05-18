<?php
require_once '../config/database.php';

$resortid = $_POST['resortid'];
$resortname = str_replace("'", "''", $_POST['resortname']);
$resortaddress = $resortname . " " . str_replace("'", "''", $_POST['resortaddress']);
$lat = $_POST['lat'];
$lon = $_POST['lon'];

$sqladdloc = "INSERT INTO tb_location(resortid, name, address, lat, lon)VALUES('$resortid', '$resortname', '$resortaddress', '$lat','$lon')";
$statement = $db->prepare($sqladdloc);
$statement->execute();

$isLocated = 1;

$updatelocated = $db->prepare("UPDATE tb_resort SET isLocated = ? WHERE resortid = ?");
$updatelocated->execute([$isLocated, $_POST['resortid']]);

echo "OK";