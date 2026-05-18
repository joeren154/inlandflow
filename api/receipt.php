<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$po_id = $_GET['po_id'] ?? 0;

if (!$po_id) {
    echo 'Invalid request - no po_id';
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $e_rec = $db->prepare("SELECT * FROM tb_placed_order a JOIN tb_cart b ON a.cart_id = b.cart_id JOIN tb_resort c ON b.resortid = c.resortid JOIN tb_guest d ON b.guest_id = d.guest_id LEFT JOIN tb_resort_room e ON b.resort_room_id = e.resort_room_id WHERE a.po_id = ?");
    $e_rec->execute([$po_id]);
    $row_rec = $e_rec->fetch(PDO::FETCH_ASSOC);
    
    if (!$row_rec) {
        echo 'Booking not found for po_id: ' . $po_id;
        exit;
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}

$sqladdondetails = $db->prepare("SELECT * FROM tb_add_on_details a JOIN tb_resort_room b ON a.resort_room_id = b.resort_room_id WHERE a.po_id = ?");
$sqladdondetails->execute([$po_id]);
$addondetails = $sqladdondetails->fetchAll();

$sqlamenity = $db->prepare("SELECT * FROM tb_add_on_amenities a JOIN tb_resort_amenities b ON a.amenity_id = b.amenity_id WHERE a.po_id = ?");
$sqlamenity->execute([$po_id]);
$amenities = $sqlamenity->fetchAll();

$sqltotal = $db->prepare("SELECT SUM(total_fee) as TotalAddons, SUM(kids_fee) as TotalKFee, SUM(adult_fee) as TotalAFee FROM tb_add_on_details WHERE po_id = ?");
$sqltotal->execute([$po_id]);
$rowTotal = $sqltotal->fetch(PDO::FETCH_ASSOC);

$sqltotalamfee = $db->prepare("SELECT SUM(total_amenity_fee) as TotalAmFee FROM tb_add_on_amenities WHERE po_id = ?");
$sqltotalamfee->execute([$po_id]);
$rowAFee = $sqltotalamfee->fetch(PDO::FETCH_ASSOC);

date_default_timezone_set("Asia/Taipei");
$year_today = date("Y");
$receipt_no = $row_rec['po_id'] . $row_rec['resortid'] . $row_rec['cart_id'] . $row_rec['resort_room_id'] . $year_today . $row_rec['guest_id'];

$total_fee = $row_rec['kids_fee'] + $row_rec['adult_fee'] + ($rowTotal['TotalKFee'] ?? 0) + ($rowTotal['TotalAFee'] ?? 0);
$TotalAddonEntrance = ($rowTotal['TotalKFee'] ?? 0) + ($rowTotal['TotalAFee'] ?? 0);
$TotalFee = ($rowTotal['TotalAddons'] ?? 0) - $TotalAddonEntrance;

$checkin = new DateTime($row_rec['checkindate']);
$checkout = new DateTime($row_rec['checkoutdate']);
$numDays = max(1, $checkout->diff($checkin)->days);

$room_price_total = ($row_rec['room_price'] ?? 0) * $numDays;
$AccomodationFee = $room_price_total + $TotalFee;
$amenities_fee = $rowAFee['TotalAmFee'] ?? 0;
$grandTotal = $total_fee + $AccomodationFee + $amenities_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Receipt - InlandFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .receipt-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border: 1px solid #ddd; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #6c5ce7; padding-bottom: 15px; }
        .header h2 { color: #6c5ce7; margin: 0; }
        .receipt-no { text-align: right; font-size: 12px; color: #666; }
        .info-section { margin: 15px 0; }
        .info-section p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #f8f9fa; }
        .total-row { font-weight: bold; background: #f8f9fa; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .footer { text-align: center; margin-top: 20px; color: #666; }
        .btn-print { background: #6c5ce7; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .btn-print:hover { background: #5b4bc9; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #6c5ce7; text-decoration: none; }
    </style>
</head>
<body>
    <a href="javascript:history.back()" class="back-link"><i class="bi bi-arrow-left"></i> Back to Bookings</a>
    
    <div class="receipt-container" id="PrintableReceipt">
        <div class="header">
            <h2><?php echo htmlspecialchars($row_rec['resortname']); ?></h2>
            <p><?php echo htmlspecialchars($row_rec['resortaddress']); ?></p>
        </div>
        
        <div class="receipt-no">
            <strong>E-Receipt No:</strong> <?php echo $receipt_no; ?>
        </div>
        
        <div class="info-section">
            <p><strong>Date:</strong> <?php echo date("M d, Y"); ?></p>
            <p><strong>Time:</strong> <?php echo date("H:i a"); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($row_rec['FirstName'] . " " . $row_rec['MiddleName'] . " " . $row_rec['LastName']); ?></p>
            <p><strong>Transaction:</strong> Reservation</p>
            <p><strong>Accommodation:</strong> <?php echo $row_rec['room_name'] ? htmlspecialchars($row_rec['room_name']) . ' x ' . $numDays . ' nights - ₱' . number_format($room_price_total) : 'None'; ?></p>
        </div>
        
        <?php if(count($addondetails) > 0): ?>
        <div class="info-section">
            <strong>Add-on Rooms:</strong>
            <?php 
            foreach($addondetails as $addon): 
                $addonCheckin = new DateTime($row_rec['checkindate']);
                $addonCheckout = new DateTime($row_rec['checkoutdate']);
                $addonNumDays = max(1, $addonCheckout->diff($addonCheckin)->days);
                $addonTotal = $addon['room_price'] * $addonNumDays;
            ?>
            <p style="margin-left: 15px;"><?php echo htmlspecialchars($addon['room_name']); ?> x <?php echo $addonNumDays; ?> nights - ₱<?php echo number_format($addonTotal); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if(count($amenities) > 0): ?>
        <div class="info-section">
            <strong>Amenities:</strong>
            <?php foreach($amenities as $am): ?>
            <p style="margin-left: 15px;"><?php echo htmlspecialchars($am['amenity_name']); ?> x<?php echo $am['quantity']; ?> - ₱<?php echo number_format($am['amenity_price']); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="info-section">
            <p><strong>Check-in:</strong> <?php echo date("M d, Y", strtotime($row_rec['checkindate'])); ?></p>
            <p><strong>Check-out:</strong> <?php echo date("M d, Y", strtotime($row_rec['checkoutdate'])); ?></p>
            <p><strong>Duration:</strong> <?php echo $numDays; ?> night(s)</p>
            <p><strong>Adults:</strong> <?php echo $row_rec['num_adults']; ?> (₱<?php echo number_format($row_rec['adult_fee']); ?>)</p>
            <p><strong>Kids:</strong> <?php echo $row_rec['num_kids']; ?> (₱<?php echo number_format($row_rec['kids_fee']); ?>)</p>
        </div>
        
        <table>
            <tr>
                <td>Entrance Fee</td>
                <td class="text-end">₱<?php echo number_format($row_rec['adult_fee'] + $row_rec['kids_fee']); ?></td>
            </tr>
            <tr>
                <td>Accommodation Fee</td>
                <td class="text-end">₱<?php echo number_format($AccomodationFee); ?></td>
            </tr>
            <tr>
                <td>Amenities Fee</td>
                <td class="text-end">₱<?php echo number_format($amenities_fee); ?></td>
            </tr>
            <tr class="total-row">
                <td>TOTAL AMOUNT</td>
                <td class="text-end"><strong>₱<?php echo number_format($grandTotal); ?></strong></td>
            </tr>
        </table>
        
        <div class="info-section">
            <p><strong>Mode of Payment:</strong> <?php echo htmlspecialchars($row_rec['payment_method']); ?></p>
            <p><strong>Status:</strong> <span style="color: green;"><?php echo htmlspecialchars($row_rec['reservation_status']); ?></span></p>
        </div>
        
        <div class="footer">
            <h4>THANK YOU!</h4>
            <p><small>This is an auto-generated electronic receipt.</small></p>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Print E-Receipt</button>
    </div>
</body>
</html>