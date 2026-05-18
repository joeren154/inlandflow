<?php
require_once '../config/database.php';

$user_id = $_SESSION['user_reg'];
$prOrders = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE c.guest_id = '$user_id'");
$prOrders->execute();
$prOrders = $prOrders->fetchColumn();

$prCart = $db->prepare("SELECT COUNT(a.cart_id) FROM tb_cart a JOIN tb_guest b ON a.guest_id = b.guest_id WHERE b.guest_id = '$user_id' AND cart_status = ''");
$prCart->execute();
$prCart = $prCart->fetchColumn();

$prReservation = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE c.resortid = '$user_id'");
$prReservation->execute();
$prReservation = $prReservation->fetchColumn();

$prRReports = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE a.resortid = '$user_id'");
$prRReports->execute();
$prRReports = $prRReports->fetchColumn();

$resortmun = $db->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$resortmun->execute([$_SESSION['user_reg']]);
$rowmunicipality = $db->fetch(PDO::FETCH_ASSOC);

$r_municipality = $rowmunicipality['mun'];

$prResorts = $db->prepare("SELECT COUNT(resortid) FROM tb_resort WHERE mun = '$r_municipality'");
$prResorts->execute();
$prResorts = $prResorts->fetchColumn();

$prReports = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.mun = '$r_municipality'");
$prReports->execute();
$prReports = $prReports->fetchColumn();

$prCountResort = $db->prepare("SELECT COUNT(resortid) FROM tb_resort");
$prCountResort->execute();
$prCountResort = $prCountResort->fetchColumn();

$prCountMunicipality = $db->prepare("SELECT COUNT(id) FROM tb_municipality");
$prCountMunicipality->execute();
$prCountMunicipality = $prCountMunicipality->fetchColumn();

$arr = array('prOrders' => ($prOrders > 100 ? "100 +" : $prOrders), 'prCart' => ($prCart > 100 ? "100 +" : $prCart), 'prReservation' => ($prReservation > 100 ? "100 +" : $prReservation), 'prRReports' => ($prRReports > 100 ? "100 +" : $prRReports), 'prResorts' => ($prResorts > 100 ? "100 +" : $prResorts), 'prReports' => ($prReports > 100 ? "100 +" : $prReports), 'prCountResort' => ($prCountResort > 100 ? "100 +" : $prCountResort), 'prCountMunicipality' => ($prCountMunicipality > 100 ? "100 +" : $prCountMunicipality));

echo json_encode($arr);