<?php
require_once '../config/database.php';

$updateLoc = $db->prepare("UPDATE tb_location SET lat = ?, lon = ? WHERE location_id = ?");
$updateLoc->execute([$_POST['lat'], $_POST['lon'], $_POST['location_id']]);

echo "OK";