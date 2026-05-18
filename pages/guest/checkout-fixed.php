<?php
// FIX #1: Add session check - ensure user is logged in
if(!isset($_SESSION['user_reg']) || $_SESSION['type_of_user'] != 'Guest') {
    header('Location: ?page=guest-login');
    exit;
}

// FIX #2: Validate that we have POST data for booking
if($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['resortid'])) {
    header('Location: ?page=resorts');
    exit;
}

$guestId = $_SESSION['user_reg'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $resortid = intval($_POST['resortid']);
    $resort_room_id = !empty($_POST['resort_room_id']) ? intval($_POST['resort_room_id']) : null;
    $checkindate = $_POST['checkindate'] ?? '';
    $checkoutdate = $_POST['checkoutdate'] ?? '';
    $num_adults = intval($_POST['num_adults'] ?? 0);
    $num_kids = intval($_POST['num_kids'] ?? 0);
    $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
    $amenity_quantities = isset($_POST['amenity_quantity']) ? $_POST['amenity_quantity'] : [];
    $payment_method = $_POST['payment_method'] ?? 'Cash on Arrival';
    $message = $_POST['message'] ?? '';
    
    // FIX #3: Validate dates on server side
    $checkin_timestamp = strtotime($checkindate);
    $checkout_timestamp = strtotime($checkoutdate);
    $today = strtotime('today');
    
    if(!$checkin_timestamp || !$checkout_timestamp) {
        $_SESSION['error'] = 'Invalid date format';
        header('Location: ?page=resorts');
        exit;
    }
    
    if($checkin_timestamp < $today) {
        $_SESSION['error'] = 'Check-in date cannot be in the past';
        header('Location: ?page=resorts');
        exit;
    }
    
    if($checkout_timestamp <= $checkin_timestamp) {
        $_SESSION['error'] = 'Check-out date must be after check-in date';
        header('Location: ?page=resorts');
        exit;
    }
    
    // FIX #4: Calculate total amount on server side - DO NOT trust client submission
    $resort = $conn->prepare("SELECT adultEntranceFee, kidsEntranceFee FROM tb_resort WHERE resortid = ?");
    $resort->execute([$resortid]);
    $fees = $resort->fetch();
    
    if(!$fees) {
        $_SESSION['error'] = 'Resort not found';
        header('Location: ?page=resorts');
        exit;
    }
    
    $adult_fee = $num_adults * $fees['adultEntranceFee'];
    $kids_fee = $num_kids * $fees['kidsEntranceFee'];
    
    // Get room price if selected
    $room_price = 0;
    if($resort_room_id) {
        $room = $conn->prepare("SELECT room_price FROM tb_resort_room WHERE resort_room_id = ? AND resortid = ?");
        $room->execute([$resort_room_id, $resortid]);
        $room_data = $room->fetch();
        $room_price = $room_data ? $room_data['room_price'] : 0;
    }
    
    // Calculate amenities total
    $amenities_total = 0;
    foreach($amenities as $amenity_id) {
        $amenity_id = intval($amenity_id);
        $quantity = isset($amenity_quantities[$amenity_id]) ? intval($amenity_quantities[$amenity_id]) : 1;
        
        $amenity = $conn->prepare("SELECT amenity_price FROM tb_resort_amenities WHERE amenity_id = ? AND resortid = ?");
        $amenity->execute([$amenity_id, $resortid]);
        $amenity_data = $amenity->fetch();
        
        if($amenity_data) {
            $price = $amenity_data['amenity_price'];
            $amenities_total += ($price * $quantity);
        }
    }
    
    // Calculate the correct total
    $calculated_total = $adult_fee + $kids_fee + $room_price + $amenities_total;
    
    // Insert cart record
    $cart = $conn->prepare("INSERT INTO tb_cart (guest_id, resortid, resort_room_id, checkindate, checkoutdate, num_adults, num_kids, cart_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $cart->execute([$guestId, $resortid, $resort_room_id, $checkindate, $checkoutdate, $num_adults, $num_kids]);
    $cartId = $conn->lastInsertId();
    
    $maxOrderId = $conn->query("SELECT MAX(po_id) as max_id FROM tb_placed_order")->fetch();
    $newOrderId = ($maxOrderId['max_id'] ?? 0) + 1;
    
    $order = $conn->prepare("INSERT INTO tb_placed_order (po_id, cart_id, adult_fee, kids_fee, total_fee, payment_method, message, reservation_status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $order->execute([$newOrderId, $cartId, $adult_fee, $kids_fee, $calculated_total, $payment_method, $message]);
    
    // Insert amenities
    foreach($amenities as $amenity_id) {
        $amenity_id = intval($amenity_id);
        $quantity = isset($amenity_quantities[$amenity_id]) ? intval($amenity_quantities[$amenity_id]) : 1;
        
        $amenity = $conn->prepare("SELECT amenity_price FROM tb_resort_amenities WHERE amenity_id = ? AND resortid = ?");
        $amenity->execute([$amenity_id, $resortid]);
        $amenity_data = $amenity->fetch();
        
        if($amenity_data) {
            $price = $amenity_data['amenity_price'];
            $total_amenity_fee = $price * $quantity;
            
            $addon = $conn->prepare("INSERT INTO tb_add_on_amenities (po_id, amenity_id, quantity, total_amenity_fee) VALUES (?, ?, ?, ?)");
            $addon->execute([$newOrderId, $amenity_id, $quantity, $total_amenity_fee]);
        }
    }
    
    $_SESSION['booking_success'] = true;
    echo '<script>window.location.href = "?page=guest-bookings&success=1";</script>';
    exit;
}

// Display checkout page - validate resort exists
$resortid = intval($_POST['resortid'] ?? 0);
if($resortid <= 0) {
    header('Location: ?page=resorts');
    exit;
}

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortid]);
$resortData = $resort->fetch();

if(!$resortData) {
    header('Location: ?page=resorts');
    exit;
}

// Get guest data
$guestStmt = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
$guestStmt->execute([$guestId]);
$guestData = $guestStmt->fetch();
?>

<div class="p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="?page=resorts" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
                <i class="bi bi-arrow-left"></i> Back to Resorts
            </a>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Title Banner -->
            <div class="bg-gradient-to-r from-purple-500 to-emerald-500 px-6 py-5">
                <h1 class="text-2xl font-bold text-white">Checkout</h1>
                <p class="text-white/80">Review your booking details</p>
            </div>
            
            <div class="p-6">
                <form method="POST" id="checkoutForm">
                    <input type="hidden" name="resortid" value="<?php echo isset($_POST['resortid']) ? htmlspecialchars($_POST['resortid']) : ''; ?>">
                    <input type="hidden" name="resort_room_id" value="<?php echo isset($_POST['resort_room_id']) ? htmlspecialchars($_POST['resort_room_id']) : ''; ?>">
                    <input type="hidden" name="checkindate" value="<?php echo isset($_POST['checkindate']) ? htmlspecialchars($_POST['checkindate']) : ''; ?>">
                    <input type="hidden" name="checkoutdate" value="<?php echo isset($_POST['checkoutdate']) ? htmlspecialchars($_POST['checkoutdate']) : ''; ?>">
                    <input type="hidden" name="num_adults" value="<?php echo isset($_POST['num_adults']) ? htmlspecialchars($_POST['num_adults']) : ''; ?>">
                    <input type="hidden" name="num_kids" value="<?php echo isset($_POST['num_kids']) ? htmlspecialchars($_POST['num_kids']) : ''; ?>">
                    
                    <!-- Booking Summary -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-calendar-check text-purple-500"></i> Booking Summary
                        </h2>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400">Resort</p>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($resortData['resortname']); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Dates</p>
                                    <p class="font-semibold text-gray-800"><?php echo date('M j, Y', strtotime($_POST['checkindate'] ?? '')); ?> - <?php echo date('M j, Y', strtotime($_POST['checkoutdate'] ?? '')); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Guests</p>
                                    <p class="font-semibold text-gray-800"><?php echo intval($_POST['num_adults'] ?? 0); ?> Adults, <?php echo intval($_POST['num_kids'] ?? 0); ?> Kids</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Total</p>
                                    <p class="text-2xl font-bold text-purple-600" id="displayTotal">₱0.00</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Guest Info -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-person text-purple-500"></i> Guest Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Full Name</p>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($guestData['FirstName'] . ' ' . $guestData['LastName']); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Contact</p>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($guestData['ContactNo']); ?></p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-400 mb-1">Address</p>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($guestData['Address']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-credit-card text-purple-500"></i> Payment Method
                        </h2>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                <input class="w-4 h-4 text-purple-600" type="radio" name="payment_method" id="cash" value="Cash on Arrival" checked>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-cash-stack text-emerald-600"></i>
                                    <span class="font-medium text-gray-700">Cash on Arrival</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                <input class="w-4 h-4 text-purple-600" type="radio" name="payment_method" id="gcash" value="GCash">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-phone text-blue-600"></i>
                                    <span class="font-medium text-gray-700">GCash</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                <input class="w-4 h-4 text-purple-600" type="radio" name="payment_method" id="bank" value="Bank Transfer">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-bank text-purple-600"></i>
                                    <span class="font-medium text-gray-700">Bank Transfer</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Special Requests -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-chat-dots text-purple-500"></i> Special Requests
                        </h2>
                        <textarea name="message" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="3" placeholder="Any special requests or notes for the resort?"></textarea>
                    </div>
                    
                    <!-- Terms -->
                    <div class="mb-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input class="w-4 h-4 mt-1 text-purple-600 rounded" type="checkbox" id="terms" required>
                            <span class="text-sm text-gray-600">
                                I agree to the <a href="#" class="text-purple-600 font-medium">Terms and Conditions</a> and cancellation policy
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#checkoutForm').on('submit', function(e) {
    if(!$('#terms').is(':checked')) {
        e.preventDefault();
        showToast('Please agree to the terms and conditions', 'error');
    }
});
</script>
