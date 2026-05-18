<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Allow resort users OR cron job access (no session required for cron)
$isResort = ($_SESSION['type_of_user'] ?? '') === 'Resort';
$isCron = isset($_GET['cron_key']) && $_GET['cron_key'] === 'inland_flow_auto_update_2026';

if (!$isResort && !$isCron) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Update reservation status to Completed where checkout date has passed
    $updateStmt = $db->prepare("
        UPDATE tb_placed_order po
        JOIN tb_cart c ON po.cart_id = c.cart_id
        SET po.reservation_status = 'Completed'
        WHERE po.reservation_status = 'Approved'
        AND c.checkoutdate < CURDATE()
    ");
    $updateStmt->execute();
    $updatedCount = $updateStmt->rowCount();
    
    // Update room status to Available for auto-completed reservations
    if ($updatedCount > 0) {
        $updateRoomsStmt = $db->prepare("
            UPDATE tb_resort_room rr
            JOIN tb_cart c ON rr.resort_room_id = c.resort_room_id
            JOIN tb_placed_order po ON c.cart_id = po.cart_id
            SET rr.room_status = 'Available'
            WHERE po.reservation_status = 'Completed'
            AND rr.room_status = 'Not Available'
        ");
        $updateRoomsStmt->execute();
    }
    
    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'updated' => $updatedCount,
        'message' => $updatedCount . ' reservation(s) auto-updated to Completed'
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
