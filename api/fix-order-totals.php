<?php
require_once '../config/database.php';

header('Content-Type: text/plain');

try {
    // Add room_fee column if not exists
    try {
        $db->exec("ALTER TABLE tb_placed_order ADD COLUMN room_fee DECIMAL(10,2) DEFAULT 0 AFTER kids_fee");
        echo "Added room_fee column\n";
    } catch (Exception $e) {
        echo "room_fee column already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Add num_days column if not exists
    try {
        $db->exec("ALTER TABLE tb_placed_order ADD COLUMN num_days INT(11) DEFAULT 1 AFTER room_fee");
        echo "Added num_days column\n";
    } catch (Exception $e) {
        echo "num_days column already exists or error: " . $e->getMessage() . "\n";
    }
    
    echo "\n--- Fixing existing orders ---\n";
    
    // Get all orders with their cart and resort info
    $orders = $db->prepare("
        SELECT po.po_id, po.cart_id, po.adult_fee, po.kids_fee, po.total_fee as current_total,
               c.resortid, c.resort_room_id, c.checkindate, c.checkoutdate, c.num_adults, c.num_kids,
               r.adultEntranceFee, r.kidsEntranceFee,
               rr.room_price
        FROM tb_placed_order po
        JOIN tb_cart c ON po.cart_id = c.cart_id
        JOIN tb_resort r ON c.resortid = r.resortid
        LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
        ORDER BY po.po_id
    ");
    $orders->execute();
    
    $fixed = 0;
    $errors = 0;
    
    while ($order = $orders->fetch(PDO::FETCH_ASSOC)) {
        try {
            // Calculate days
            $checkin = new DateTime($order['checkindate']);
            $checkout = new DateTime($order['checkoutdate']);
            $numDays = max(1, $checkout->diff($checkin)->days);
            
            // Calculate adult and kids fees
            $adult_fee = $order['num_adults'] * $order['adultEntranceFee'];
            $kids_fee = $order['num_kids'] * $order['kidsEntranceFee'];
            
            // Calculate room fee (price x days)
            $room_fee = ($order['room_price'] ?? 0) * $numDays;
            
            // Get add-on rooms total
            $addonRooms = $db->prepare("SELECT COALESCE(SUM(total_fee), 0) FROM tb_add_on_details WHERE po_id = ?");
            $addonRooms->execute([$order['po_id']]);
            $addonRoomsTotal = (float)$addonRooms->fetchColumn();
            
            // Get add-on amenities total
            $addonAmenities = $db->prepare("SELECT COALESCE(SUM(total_amenity_fee), 0) FROM tb_add_on_amenities WHERE po_id = ?");
            $addonAmenities->execute([$order['po_id']]);
            $addonAmenitiesTotal = (float)$addonAmenities->fetchColumn();
            
            // Calculate new total
            $newTotal = $adult_fee + $kids_fee + $room_fee + $addonRoomsTotal + $addonAmenitiesTotal;
            
            // Update the order
            $update = $db->prepare("UPDATE tb_placed_order SET total_fee = ?, room_fee = ?, num_days = ? WHERE po_id = ?");
            $update->execute([$newTotal, $room_fee, $numDays, $order['po_id']]);
            
            echo "Fixed order #{$order['po_id']}: {$order['checkindate']} to {$order['checkoutdate']} ({$numDays} days) = ₱" . number_format($newTotal, 2) . "\n";
            $fixed++;
        } catch (Exception $e) {
            echo "Error fixing order #{$order['po_id']}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n--- Summary ---\n";
    echo "Total orders processed: " . ($fixed + $errors) . "\n";
    echo "Successfully fixed: {$fixed}\n";
    echo "Errors: {$errors}\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}
?>