<?php
require_once '../config/database.php';

$districtAll = $db->prepare("SELECT COUNT(id) FROM tb_municipality");
$districtAll->execute();
$districtAll = $districtAll->fetchColumn();

$districtFirst = $db->prepare("SELECT COUNT(id) FROM tb_municipality WHERE district = 'FIRST DISTRICT'");
$districtFirst->execute();
$districtFirst = $districtFirst->fetchColumn();

$districtSecond = $db->prepare("SELECT COUNT(id) FROM tb_municipality WHERE district = 'SECOND DISTRICT'");
$districtSecond->execute();
$districtSecond = $districtSecond->fetchColumn();

$districtThird = $db->prepare("SELECT COUNT(id) FROM tb_municipality WHERE district = 'THIRD DISTRICT'");
$districtThird->execute();
$districtThird = $districtThird->fetchColumn();

$districtFourth = $db->prepare("SELECT COUNT(id) FROM tb_municipality WHERE district = 'FOURTH DISTRICT'");
$districtFourth->execute();
$districtFourth = $districtFourth->fetchColumn();

$districtFifth = $db->prepare("SELECT COUNT(id) FROM tb_municipality WHERE district = 'FIFTH DISTRICT'");
$districtFifth->execute();
$districtFifth = $districtFifth->fetchColumn();

$arr = array('districtAll' => ($districtAll > 100 ? "100 +" : $districtAll), 'districtFirst' => ($districtFirst > 100 ? "100 +" : $districtFirst), 'districtSecond' => ($districtSecond > 100 ? "100 +" : $districtSecond), 'districtThird' => ($districtThird > 100 ? "100 +" : $districtThird), 'districtFourth' => ($districtFourth > 100 ? "100 +" : $districtFourth), 'districtFifth' => ($districtFifth > 100 ? "100 +" : $districtFifth));

echo json_encode($arr);