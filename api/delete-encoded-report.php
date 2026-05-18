<?php
require_once '../config/database.php';

$iddelete = $_POST['id'];

$deleteEncoded = $db->prepare("DELETE FROM tb_report WHERE id = ?");
$deleteEncoded->execute([$iddelete]);

echo "OK";