<?php
require_once '../config/database.php';

$iddelete = $_POST['resort_report_id'];

$deleteR_Report = $db->prepare("DELETE FROM tb_resort_report WHERE resort_report_id = ?");
$deleteR_Report->execute([$iddelete]);

echo "OK";