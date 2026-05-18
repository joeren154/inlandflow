<?php
require_once '../config/database.php';

$user_id = $_SESSION['user_reg'];
$reportAll = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.resortid = '$user_id'");
$reportAll->execute();
$reportAll = $reportAll->fetchColumn();

$reportPending = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.resortid = '$user_id' AND a.rstatus = 'Pending'");
$reportPending->execute();
$reportPending = $reportPending->fetchColumn();

$reportValidated = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.resortid = '$user_id' AND a.rstatus = 'Validated'");
$reportValidated->execute();
$reportValidated = $reportValidated->fetchColumn();

$reportInvalid = $db->prepare("SELECT COUNT(a.id) FROM tb_invalid a JOIN tb_report b ON a.reportid = b.id WHERE b.resortid = '$user_id' AND b.rstatus = 'Invalid'");
$reportInvalid->execute();
$reportInvalid = $reportInvalid->fetchColumn();

$arr = array('reportAll' => ($reportAll > 100 ? "100 +" : $reportAll), 'reportPending' => ($reportPending > 100 ? "100 +" : $reportPending), 'reportValidated' => ($reportValidated > 100 ? "100 +" : $reportValidated), 'reportInvalid' => ($reportInvalid > 100 ? "100 +" : $reportInvalid));

echo json_encode($arr);