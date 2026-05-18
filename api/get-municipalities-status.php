<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$stmt = $db->query("SELECT id, mun, district, last_activity FROM tb_municipality ORDER BY mun");
$municipalities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$now = new DateTime();
$fiveMinutesAgo = new DateTime('-5 minutes');

$result = [];
foreach($municipalities as $mun) {
    $lastActivity = $mun['last_activity'] ? new DateTime($mun['last_activity']) : null;
    
    if($lastActivity && $lastActivity > $fiveMinutesAgo) {
        $status = 'Active';
    } else {
        $status = 'Offline';
    }
    
    $result[] = [
        'id' => $mun['id'],
        'mun' => $mun['mun'],
        'district' => $mun['district'],
        'status' => $status,
        'last_activity' => $mun['last_activity']
    ];
}

echo json_encode(['data' => $result]);