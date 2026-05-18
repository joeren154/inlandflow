<?php
$guestId = $_SESSION['user_reg'] ?? null;
$isLoggedIn = $guestId && ($_SESSION['type_of_user'] ?? '') === 'Guest';

$guestData = null;
if($isLoggedIn) {
    $guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
    $guest->execute([$guestId]);
    $guestData = $guest->fetch();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cartId = (int)($_POST['cart_id'] ?? 0);
    $resortid = (int)($_POST['resortid'] ?? 0);
    $resort_room_id = !empty($_POST['resort_room_id']) ? (int)$_POST['resort_room_id'] : 0;
    $checkindate = $_POST['checkindate'] ?? '';
    $checkoutdate = $_POST['checkoutdate'] ?? '';
    $num_adults = (int)($_POST['num_adults'] ?? 1);
    $num_kids = (int)($_POST['num_kids'] ?? 0);
    $total_amount = (float)($_POST['total_amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'Cash on Arrival';
    $message = $_POST['message'] ?? '';
    
    if (!$resortid || !$checkindate || !$checkoutdate) {
        echo '<script>window.location.href = "index.php?page=resorts";</script>';
        exit;
    }
    
    if(!$isLoggedIn) {
        $_SESSION['checkout_pending'] = [
            'resortid' => $resortid,
            'resort_room_id' => $resort_room_id,
            'checkindate' => $checkindate,
            'checkoutdate' => $checkoutdate,
            'num_adults' => $num_adults,
            'num_kids' => $num_kids,
            'total_amount' => $total_amount,
            'payment_method' => $payment_method,
            'message' => $message,
        ];
        echo '<script>window.location.href = "login/guest-login.php?redirect=checkout";</script>';
        exit;
    }
    
    if($cartId > 0) {
        $checkExisting = $conn->prepare("SELECT po_id FROM tb_placed_order WHERE cart_id = ?");
        $checkExisting->execute([$cartId]);
        if($checkExisting->fetch()) {
            header('Location: index.php?page=guest-bookings&error=already');
            exit;
        }
        
        if($isLoggedIn) {
            $update = $conn->prepare("UPDATE tb_cart SET cart_status = 'Place Order' WHERE cart_id = ? AND guest_id = ?");
            $update->execute([$cartId, $guestId]);
        } else {
            foreach($_SESSION['guest_cart'] as $key => $item) {
                if($item['cart_id'] == $cartId || $item['cart_id'] == 'temp_' . $cartId) {
                    unset($_SESSION['guest_cart'][$key]);
                    $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']);
                    break;
                }
            }
        }
    } else {
        $maxId = $conn->query("SELECT MAX(cart_id) as max_id FROM tb_cart")->fetch();
        $newCartId = ($maxId['max_id'] ?? 0) + 1;
        
        $cart = $conn->prepare("INSERT INTO tb_cart (cart_id, guest_id, resortid, resort_room_id, checkindate, checkoutdate, num_adults, num_kids, cart_status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Place Order')");
        $cart->execute([$newCartId, $guestId, $resortid, $resort_room_id, $checkindate, $checkoutdate, $num_adults, $num_kids]);
        $cartId = $newCartId;
    }
    
    $resort = $conn->prepare("SELECT adultEntranceFee, kidsEntranceFee FROM tb_resort WHERE resortid = ?");
    $resort->execute([$resortid]);
    $fees = $resort->fetch();
    
    $checkin_dt = new DateTime($checkindate);
    $checkout_dt = new DateTime($checkoutdate);
    $numDays = max(1, $checkout_dt->diff($checkin_dt)->days);
    
    $adult_fee = $num_adults * ($fees['adultEntranceFee'] ?? 0);
    $kids_fee = $num_kids * ($fees['kidsEntranceFee'] ?? 0);
    
    $room_fee = 0;
    if ($resort_room_id > 0) {
        $roomStmt = $conn->prepare("SELECT room_price FROM tb_resort_room WHERE resort_room_id = ?");
        $roomStmt->execute([$resort_room_id]);
        $roomData = $roomStmt->fetch();
        if ($roomData) {
            $room_fee = $roomData['room_price'] * $numDays;
        }
    }
    
    $total_amount = $adult_fee + $kids_fee + $room_fee;
    
    $maxOrderId = $conn->query("SELECT MAX(po_id) as max_id FROM tb_placed_order")->fetch();
    $newOrderId = ($maxOrderId['max_id'] ?? 0) + 1;
    
    $columns = "po_id, cart_id, adult_fee, kids_fee, total_fee, payment_method, message, reservation_status";
    $values = "?, ?, ?, ?, ?, ?, ?, 'Pending'";
    $params = [$newOrderId, $cartId, $adult_fee, $kids_fee, $total_amount, $payment_method, $message];
    
    try {
        $conn->query("SELECT room_fee FROM tb_placed_order LIMIT 0");
        $columns = "po_id, cart_id, adult_fee, kids_fee, room_fee, num_days, total_fee, payment_method, message, reservation_status";
        $values = "?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending'";
        $params = [$newOrderId, $cartId, $adult_fee, $kids_fee, $room_fee, $numDays, $total_amount, $payment_method, $message];
    } catch (Exception $e) {}
    
    $order = $conn->prepare("INSERT INTO tb_placed_order ($columns) VALUES ($values)");
    $order->execute($params);
    
    echo '<script>window.location.href = "index.php?page=guest-bookings&success=1";</script>';
    exit;
}

if(isset($_SESSION['checkout_pending']) && !$isLoggedIn) {
    $pending = $_SESSION['checkout_pending'];
    $resortid = $pending['resortid'];
    $resort_room_id = $pending['resort_room_id'] ?? 0;
    $checkindate = $pending['checkindate'];
    $checkoutdate = $pending['checkoutdate'];
    $adults = $pending['num_adults'];
    $kids = $pending['num_kids'];
} else {
    $resortid = (int)($_GET['resort'] ?? 0);
    if (!$resortid) {
        $resortid = (int)($_POST['resortid'] ?? 0);
    }
    
    $resort_room_id = (int)($_GET['room'] ?? $_POST['resort_room_id'] ?? 0);
    $checkindate = $_GET['checkin'] ?? $_POST['checkindate'] ?? '';
    $checkoutdate = $_GET['checkout'] ?? $_POST['checkoutdate'] ?? '';
    $adults = (int)($_GET['adults'] ?? $_POST['num_adults'] ?? 1);
    $kids = (int)($_GET['kids'] ?? $_POST['num_kids'] ?? 0);
}

if (!$resortid) {
    echo '<script>window.location.href = "index.php?page=resorts";</script>';
    exit;
}

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortid]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<script>window.location.href = "index.php?page=resorts";</script>';
    exit;
}

$adultFee = (float)($resortData['adultEntranceFee'] ?? 0);
$kidsFee = (float)($resortData['kidsEntranceFee'] ?? 0);

$checkinDate = new DateTime($checkindate);
$checkoutDate = new DateTime($checkoutdate);
$numDays = max(1, $checkoutDate->diff($checkinDate)->days);

$calculatedTotal = ($adults * $adultFee) + ($kids * $kidsFee);

$roomData = null;
if ($resort_room_id > 0) {
    $room = $conn->prepare("SELECT room_price FROM tb_resort_room WHERE resort_room_id = ?");
    $room->execute([$resort_room_id]);
    $roomData = $room->fetch();
    if ($roomData) {
        $calculatedTotal += $roomData['room_price'] * $numDays;
    }
}
?>

<div class="p-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="index.php?page=guest-resorts" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
                <i class="bi bi-arrow-left"></i> Back to Resorts
            </a>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-emerald-500 px-6 py-5">
                <h1 class="text-2xl font-bold text-white">Checkout</h1>
                <p class="text-white/80">Review your booking details</p>
            </div>
            
            <div class="p-6">
                <form method="POST" action="index.php?page=checkout">
                    <input type="hidden" name="resortid" value="<?php echo $resortid; ?>">
                    <input type="hidden" name="resort_room_id" value="<?php echo (int)$resort_room_id; ?>">
                    <input type="hidden" name="checkindate" value="<?php echo htmlspecialchars($checkindate); ?>">
                    <input type="hidden" name="checkoutdate" value="<?php echo htmlspecialchars($checkoutdate); ?>">
                    <input type="hidden" name="num_adults" value="<?php echo $adults; ?>">
                    <input type="hidden" name="num_kids" value="<?php echo $kids; ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $calculatedTotal; ?>">
                    
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
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($checkindate ?: 'N/A'); ?> - <?php echo htmlspecialchars($checkoutdate ?: 'N/A'); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Guests</p>
                                    <p class="font-semibold text-gray-800"><?php echo $adults; ?> Adult, <?php echo $kids; ?> Kid</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Total</p>
                                    <p class="text-2xl font-bold text-purple-600">₱<?php echo number_format($calculatedTotal, 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-person text-purple-500"></i> Guest Information
                        </h2>
                        <?php if($isLoggedIn && $guestData): ?>
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
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($guestData['Address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <p class="text-amber-800"><i class="bi bi-exclamation-triangle me-2"></i>You will need to login or create an account to complete the booking.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-credit-card text-purple-500"></i> Payment Method
                        </h2>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                                <input class="w-4 h-4 text-purple-600" type="radio" name="payment_method" value="Cash on Arrival" checked>
                                <span class="font-medium text-gray-700">Cash on Arrival</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                                <input class="w-4 h-4 text-purple-600" type="radio" name="payment_method" value="GCash">
                                <span class="font-medium text-gray-700">GCash</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-chat-dots text-purple-500"></i> Special Requests
                        </h2>
                        <textarea name="message" class="w-full px-4 py-3 border border-gray-200 rounded-xl" rows="3" placeholder="Any special requests?"></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input class="w-4 h-4 mt-1 text-purple-600 rounded" type="checkbox" id="terms" required>
                            <span class="text-sm text-gray-600">I agree to the terms and conditions</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
                        <i class="bi bi-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>