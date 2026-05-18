<?php
session_start();

$userType = isset($_SESSION['type_of_user']) ? $_SESSION['type_of_user'] : null;
$userId = isset($_SESSION['user_reg']) ? $_SESSION['user_reg'] : null;

if($userId) {
    require_once '../connection.php';
    if($userType == 'Municipal') {
        try {
            $conn->query("ALTER TABLE tb_municipality ADD COLUMN IF NOT EXISTS last_activity DATETIME DEFAULT NULL");
        } catch (Exception $e) {}
        $stmt = $conn->prepare("UPDATE tb_municipality SET last_activity = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif($userType == 'Resort') {
        try {
            $conn->query("ALTER TABLE tb_resort ADD COLUMN IF NOT EXISTS last_activity DATETIME DEFAULT NULL");
        } catch (Exception $e) {}
        $stmt = $conn->prepare("UPDATE tb_resort SET last_activity = NULL, status = 'Offline now' WHERE resortid = ?");
        $stmt->execute([$userId]);
    }
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header('Location: ../index.php');
exit;