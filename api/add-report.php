<?php
require_once '../config/database.php';

$resortid = (int)$_POST['resortid'];
$male_domestic = (int)$_POST['male_domestic'];
$female_domestic = (int)$_POST['female_domestic'];
$dcx_quan = $male_domestic + $female_domestic;
$male_foreign = (int)$_POST['male_foreign'];
$female_foreign = (int)$_POST['female_foreign'];
$fcx_quan = $male_foreign + $female_foreign;
$total_customer = $dcx_quan + $fcx_quan;
$rdate = $_POST['rdate'] . "-01";
$rsales = (float)$_POST['rsales'];
$rexpenses = (float)$_POST['rexpenses'];

$nextId = $db->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM tb_report")->fetch()['next_id'];
$nextReportId = $db->query("SELECT COALESCE(MAX(reportid), 1000) + 1 AS next_id FROM tb_report")->fetch()['next_id'];
$sqladdreport = "INSERT INTO tb_report(id, reportid, resortid, male_domestic, female_domestic, dcx_quan, male_foreign, female_foreign, fcx_quan, total_customer, rdate, rsales, rexpenses, rstatus) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$statement = $db->prepare($sqladdreport);
$statement->execute([$nextId, $nextReportId, $resortid, $male_domestic, $female_domestic, $dcx_quan, $male_foreign, $female_foreign, $fcx_quan, $total_customer, $rdate, $rsales, $rexpenses]);
echo "OK";