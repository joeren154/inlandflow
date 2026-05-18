<?php
// Helper functions for the application

function getFeaturedResorts($limit = 8) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM tb_resort WHERE isFeatured = 1 AND isLocated = 1 LIMIT :limit");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllResorts() {
    global $db;
    $stmt = $db->query("SELECT r.*, COUNT(i.id) as image_count FROM tb_resort r LEFT JOIN images i ON r.resortid = i.resortid WHERE r.isLocated = 1 GROUP BY r.resortid");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResortById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getResortImages($resortId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM images WHERE resortid = ?");
    $stmt->execute([$resortId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResortRooms($resortId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM tb_resort_room WHERE resortid = ? AND room_status = 'Available'");
    $stmt->execute([$resortId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResortAmenities($resortId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ?");
    $stmt->execute([$resortId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGuestBookings($guestId) {
    global $db;
    $stmt = $db->prepare("
        SELECT c.*, po.*, r.resortname, r.resortaddress 
        FROM tb_cart c 
        JOIN tb_placed_order po ON c.cart_id = po.cart_id 
        JOIN tb_resort r ON c.resortid = r.resortid 
        WHERE c.guest_id = ? 
        ORDER BY c.checkindate DESC
    ");
    $stmt->execute([$guestId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResortReservations($resortId, $status = null) {
    global $db;
    $sql = "
        SELECT c.*, po.*, g.FirstName, g.LastName, g.ContactNo 
        FROM tb_cart c 
        JOIN tb_placed_order po ON c.cart_id = po.cart_id 
        JOIN tb_guest g ON c.guest_id = g.guest_id 
        WHERE c.resortid = ?
    ";
    if($status) {
        $sql .= " AND po.reservation_status = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$resortId, $status]);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute([$resortId]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResortStaff($resortId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM tb_staff WHERE resortid = ? ORDER BY hire_date DESC");
    $stmt->execute([$resortId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResortTasks($resortId) {
    global $db;
    $stmt = $db->prepare("
        SELECT t.*, s.first_name, s.last_name 
        FROM tb_task_assignment t 
        JOIN tb_staff s ON t.staff_id = s.staff_id 
        WHERE t.resortid = ? 
        ORDER BY t.due_date ASC
    ");
    $stmt->execute([$resortId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMunicipalResorts($municipalId) {
    global $db;
    $municipal = getUserData();
    $stmt = $db->prepare("SELECT * FROM tb_resort WHERE mun = ?");
    $stmt->execute([$municipal['mun']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPendingReports($municipalId = null) {
    global $db;
    if($municipalId) {
        $municipal = getUserData();
        $stmt = $db->prepare("
            SELECT r.*, rs.resortname 
            FROM tb_report r 
            JOIN tb_resort rs ON r.resortid = rs.resortid 
            WHERE rs.mun = ? AND r.rstatus = 'Pending'
        ");
        $stmt->execute([$municipal['mun']]);
    } else {
        $stmt = $db->query("
            SELECT r.*, rs.resortname, rs.mun 
            FROM tb_report r 
            JOIN tb_resort rs ON r.resortid = rs.resortid 
            WHERE r.rstatus = 'Pending'
        ");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function getStatusBadge($status) {
    $badges = [
        'Pending' => 'bg-yellow-100 text-yellow-800',
        'Approved' => 'bg-blue-100 text-blue-800',
        'Completed' => 'bg-green-100 text-green-800',
        'Rejected' => 'bg-red-100 text-red-800',
        'Validated' => 'bg-green-100 text-green-800',
        'Invalid' => 'bg-red-100 text-red-800'
    ];
    $class = $badges[$status] ?? 'bg-gray-100 text-gray-800';
    return "<span class='px-2 py-1 rounded-full text-xs font-semibold $class'>$status</span>";
}
?>