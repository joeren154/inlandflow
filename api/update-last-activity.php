<?php
require_once '../config/database.php';

$id = $_POST['id'] ?? null;

if($id) {
    $stmt = $db->prepare("UPDATE tb_municipality SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    echo "OK";
}