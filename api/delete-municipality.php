<?php
require_once '../config/database.php';

$iddelete = $_POST['id'];

$deleteMunicipality = $db->prepare("DELETE FROM tb_municipality WHERE id = ?");
$deleteMunicipality->execute([$iddelete]);

echo "OK";