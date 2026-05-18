<?php
require_once '../config/database.php';

$resortmun = $db->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$resortmun->execute([$_SESSION['user_reg']]);
$rowmunicipality = $db->fetch(PDO::FETCH_ASSOC);

$r_municipality = $rowmunicipality['mun'];

$reportAll = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.mun = '$r_municipality'");
$reportAll->execute();
$reportAll = $reportAll->fetchColumn();

$reportPending = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.mun = '$r_municipality' AND a.rstatus = 'Pending'");
$reportPending->execute();
$reportPending = $reportPending->fetchColumn();

$reportValidated = $db->prepare("SELECT COUNT(a.id) FROM tb_report a JOIN tb_resort b ON a.resortid = b.resortid WHERE b.mun = '$r_municipality' AND a.rstatus = 'Validated'");
$reportValidated->execute();
$reportValidated = $reportValidated->fetchColumn();

$reportInvalid = $db->prepare("SELECT COUNT(a.id) FROM tb_invalid a JOIN tb_report b ON a.reportid = b.id WHERE a.mun = '$r_municipality' AND b.rstatus = 'Invalid'");
$reportInvalid->execute();
$reportInvalid = $reportInvalid->fetchColumn();

$arr = array('reportAll' => ($reportAll > 100 ? "100 +" : $reportAll), 'reportPending' => ($reportPending > 100 ? "100 +" : $reportPending), 'reportValidated' => ($reportValidated > 100 ? "100 +" : $reportValidated), 'reportInvalid' => ($reportInvalid > 100 ? "100 +" : $reportInvalid));

echo json_encode($arr);