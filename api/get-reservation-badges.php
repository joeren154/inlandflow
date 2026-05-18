<?php
require_once '../config/database.php';

$resortid = $_SESSION['user_reg'];
$poPending = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE a.reservation_status = 'Pending' AND c.resortid = '$resortid'");
$poPending->execute();
$poPending = $poPending->fetchColumn();

$poPayApproval = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE a.reservation_status = 'PaymentApproval' AND c.resortid = '$resortid'");
$poPayApproval->execute();
$poPayApproval = $poPayApproval->fetchColumn();

$poApproved = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE a.reservation_status = 'Approved' AND c.resortid = '$resortid'");
$poApproved->execute();
$poApproved = $poApproved->fetchColumn();

$poCompleted = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE a.reservation_status = 'Completed' OR a.reservation_status = 'Reviewed' AND c.resortid = '$resortid'");
$poCompleted->execute();
$poCompleted = $poCompleted->fetchColumn();

$poReviewed = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE a.reservation_status = 'Reviewed' AND c.resortid = '$resortid'");
$poReviewed->execute();
$poReviewed = $poReviewed->fetchColumn();

$poRejected = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid WHERE a.reservation_status = 'Rejected' AND c.resortid = '$resortid'");
$poRejected->execute();
$poRejected = $poRejected->fetchColumn();

$arr = array('poPending' => ($poPending > 100 ? "100 +" : $poPending), 'poPayApproval' => ($poPayApproval > 100 ? "100 +" : $poPayApproval), 'poApproved' => ($poApproved > 100 ? "100 +" : $poApproved), 'poCompleted' => ($poCompleted > 100 ? "100 +" : $poCompleted), 'poReviewed' => ($poReviewed > 100 ? "100 +" : $poReviewed), 'poRejected' => ($poRejected > 100 ? "100 +" : $poRejected));

echo json_encode($arr);