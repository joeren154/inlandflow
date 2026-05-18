<?php
require_once '../config/database.php';

$resortname = str_replace("'", "''", $_POST['resortname']);
$resortaddress = str_replace("'", "''", $_POST['resortaddress']);
$mun = str_replace("'", "''", $_POST['mun']);
$district = str_replace("'", "''", $_POST['district']);
$contact_no = $_POST['contact_no'];
$username = str_replace("'", "''", $_POST['username']);
$password = str_replace("'", "''", $_POST['password']);

$nextResortId = $db->query("SELECT COALESCE(MAX(resortid), 0) + 1 AS next_id FROM tb_resort")->fetch()['next_id'];
$sqladdresort = "INSERT INTO tb_resort(resortid, resortname, resortaddress, mun, district, contact_no, username, password, status)VALUES(?, ?, ?, ?, ?, ?, ?, ?, 'Offline now')";
$statement = $db->prepare($sqladdresort);
$statement->execute([$nextResortId, $resortname, $resortaddress, $mun, $district, $contact_no, $username, $password]);
echo "OK";