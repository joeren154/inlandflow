<?php
require_once '../config/database.php';

$resortname = str_replace("'", "''", $_POST['resortname']);
$resortaddress = str_replace("'", "''", $_POST['resortaddress']);
$username = str_replace("'", "''", $_POST['username']);
$password = str_replace("'", "''", $_POST['password']);

$updateResort = $db->prepare("UPDATE tb_resort SET
resortname = ?, resortaddress=?, contact_no=?, username = ?, password = ?
WHERE resortid = ?");

$updateResort->execute(
    [
        $resortname,
        $resortaddress,
        $_POST['contact_no'],
        $username,
        $password,
        $_POST['resortid']
    ]
);

echo "OK";