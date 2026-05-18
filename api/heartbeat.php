<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['type_of_user']) && isset($_SESSION['user_reg'])) {
    if($_SESSION['type_of_user'] == 'Municipal') {
        $stmt = $db->prepare("UPDATE tb_municipality SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_reg']]);
        echo json_encode(['status' => 'ok']);
    } elseif($_SESSION['type_of_user'] == 'Resort') {
        $stmt = $db->prepare("UPDATE tb_resort SET last_activity = NOW() WHERE resortid = ?");
        $stmt->execute([$_SESSION['user_reg']]);
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'error']);
}