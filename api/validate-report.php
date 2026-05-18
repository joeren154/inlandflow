<?php
session_start();
require_once '../connection.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report_id'], $_POST['action'])) {
    $reportId = (int)$_POST['report_id'];
    $action = $_POST['action'];
    $reason = $_POST['reason'] ?? '';
    
    if($action == 'validate') {
        $update = $conn->prepare("UPDATE tb_report SET rstatus = 'Validated', date_validated = NOW() WHERE id = ? AND rstatus = 'Pending'");
        $update->execute([$reportId]);
        
        echo json_encode(['status' => 'success', 'message' => 'Report validated successfully']);
    } elseif($action == 'reject') {
        $update = $conn->prepare("UPDATE tb_report SET rstatus = 'Invalid' WHERE id = ? AND rstatus = 'Pending'");
        $update->execute([$reportId]);
        
        // Add to invalid table
        $report = $conn->prepare("SELECT r.*, rs.resortname, rs.mun, rs.district FROM tb_report r JOIN tb_resort rs ON r.resortid = rs.resortid WHERE r.id = ?");
        $report->execute([$reportId]);
        $rep = $report->fetch();
        
        $insert = $conn->prepare("INSERT INTO tb_invalid (reportid, resortname, mun, district, remarks) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$reportId, $rep['resortname'], $rep['mun'], $rep['district'], $reason]);
        
        echo json_encode(['status' => 'success', 'message' => 'Report rejected']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
}
?>