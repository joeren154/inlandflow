<?php
require_once '../config/database.php';

$updateMunicipality = $db->prepare("UPDATE tb_municipality SET district = ?, mun = ?, username = ?, password = ? WHERE id = ?");
$updateMunicipality->execute([$_POST['district'], $_POST['mun'], $_POST['username'], $_POST['password'], $_POST['id']]);

echo "OK";