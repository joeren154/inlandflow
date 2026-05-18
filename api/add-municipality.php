<?php
require_once '../config/database.php';

$district = $_POST['district'];
$mun = $_POST['mun'];
$username = $_POST['username'];
$password = $_POST['password'];

$mun_sql = $db->prepare("SELECT COUNT(id) FROM tb_municipality WHERE username = ?");
$mun_sql->execute([$username]);
$count = $mun_sql->fetchColumn();

if($count == 0) {
    $sqladdmunicipality = "INSERT INTO tb_municipality(district, username, mun, password)VALUES('$district', '$username', '$mun','$password')";
    $statement = $db->prepare($sqladdmunicipality);
    $statement->execute();
    echo "OK";
} else {
    echo "Username in use. Please set another username.";
}