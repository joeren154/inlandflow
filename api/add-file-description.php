<?php
require_once '../config/database.php';

$updateDesc = $db->prepare("UPDATE images SET file_description = ? WHERE id = ?");
$updateDesc->execute([$_POST['file_description'], $_POST['imageid']]);

echo "OK";