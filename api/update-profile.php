<?php
require_once '../config/database.php';

$LastName = str_replace("'", "''", $_POST['LastName']);
$FirstName = str_replace("'", "''", $_POST['FirstName']);
$MiddleName = str_replace("'", "''", $_POST['MiddleName']);
$ContactNo = $_POST['ContactNo'];
$Address = str_replace("'", "''", $_POST['Address']);

$updateProfile = $db->prepare("UPDATE tb_guest SET LastName = ?, FirstName = ?, MiddleName = ?, ContactNo = ?, Address = ? WHERE guest_id = ?");
$updateProfile->execute([$LastName, $FirstName, $MiddleName, $ContactNo, $Address, $_SESSION['user_reg']]);

echo "OK";