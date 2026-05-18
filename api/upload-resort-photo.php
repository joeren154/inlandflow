<?php
require_once '../config/database.php';

$resortid = $_SESSION['user_reg'];

$data = rtrim(ltrim($_POST["ImageFile"], 'url("'), ')"');

$image_array_1 = explode(";", $data);
$image_array_2 = explode(",", $image_array_1[1]);

$data = base64_decode($image_array_2[1]);

$ImageFile = $resortid . '.png';

$sqladdphoto = "INSERT INTO images(resortid, file_name)VALUES('$resortid', '$ImageFile')";
$statement = $db->prepare($sqladdphoto);
$statement->execute();
echo "OK";