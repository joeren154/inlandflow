<?php
require_once '../config/database.php';

$guest_id = $_SESSION['user_reg'];
$poPending = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE a.reservation_status = 'Pending' AND c.guest_id = '$guest_id'");
$poPending->execute();
$poPending = $poPending->fetchColumn();

$poPayApproval = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE a.reservation_status = 'PaymentApproval' AND c.guest_id = '$guest_id'");
$poPayApproval->execute();
$poPayApproval = $poPayApproval->fetchColumn();

$poApproved = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE a.reservation_status = 'Approved' AND c.guest_id = '$guest_id'");
$poApproved->execute();
$poApproved = $poApproved->fetchColumn();

$poCompleted = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE a.reservation_status = 'Completed' AND c.guest_id = '$guest_id'");
$poCompleted->execute();
$poCompleted = $poCompleted->fetchColumn();

$poReviewed = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE a.reservation_status = 'Reviewed' AND c.guest_id = '$guest_id'");
$poReviewed->execute();
$poReviewed = $poReviewed->fetchColumn();

$poRejected = $db->prepare("SELECT COUNT(a.po_id) FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_guest c ON b.guest_id = c.guest_id WHERE a.reservation_status = 'Rejected' AND c.guest_id = '$guest_id'");
$poRejected->execute();
$poRejected = $poRejected->fetchColumn();

$arr = array('poPending' => ($poPending > 100 ? "100 +" : $poPending), 'poPayApproval' => ($poPayApproval > 100 ? "100 +" : $poPayApproval), 'poApproved' => ($poApproved > 100 ? "100 +" : $poApproved), 'poCompleted' => ($poCompleted > 100 ? "100 +" : $poCompleted), 'poReviewed' => ($poReviewed > 100 ? "100 +" : $poReviewed), 'poRejected' => ($poRejected > 100 ? "100 +" : $poRejected));

echo json_encode($arr);