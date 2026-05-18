<?php
require_once '../config/database.php';

$male_domestic = $_POST['male_domestic'];
$female_domestic = $_POST['female_domestic'];
$dcx_quan = $male_domestic + $female_domestic;
$male_foreign = $_POST['male_foreign'];
$female_foreign = $_POST['female_foreign'];
$fcx_quan = $male_foreign + $female_foreign;
$total_customer = $dcx_quan + $fcx_quan;
$rdate = $_POST['rdate'];
$rsales = $_POST['rsales'];
$rexpenses = $_POST['rexpenses'];
$id = $_POST['id'];

$updateReport = $db->prepare("UPDATE tb_report SET male_domestic = ?, female_domestic = ?, dcx_quan = ?, male_foreign = ?, female_foreign = ?, fcx_quan = ?, total_customer = ?, rdate = ?, rsales = ?, rexpenses = ? WHERE id = ?");
$updateReport->execute([$_POST['male_domestic'], $_POST['female_domestic'], $dcx_quan, $_POST['male_foreign'], $_POST['female_foreign'], $fcx_quan, $total_customer, $rdate, $rsales, $rexpenses, $id]);

echo "OK";