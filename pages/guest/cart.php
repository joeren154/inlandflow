<?php

require_once '././config/database.php';
$conn = $db;

$guestId = $_SESSION['user_reg'] ?? null;
$isLoggedIn = $guestId && ($_SESSION['type_of_user'] ?? '') === 'Guest';

$guestData = null;
if($isLoggedIn) {
    $guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
    $guest->execute([$guestId]);
    $guestData = $guest->fetch();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $resortid = (int)($_POST['resortid'] ?? 0);
    $resort_room_id = !empty($_POST['resort_room_id']) ? (int)$_POST['resort_room_id'] : 0;
    $checkindate = $_POST['checkindate'] ?? '';
    $checkoutdate = $_POST['checkoutdate'] ?? '';
    $num_adults = (int)($_POST['num_adults'] ?? 1);
    $num_kids = (int)($_POST['num_kids'] ?? 0);
    $total_amount = (float)($_POST['total_amount'] ?? 0);

    if($isLoggedIn) {
        $maxIdStmt = $conn->query("SELECT COALESCE(MAX(cart_id), 0) as max_id FROM tb_cart");
        $maxIdRow = $maxIdStmt->fetch();
        $nextCartId = $maxIdRow['max_id'] + 1;
        
        $insert = $conn->prepare("INSERT INTO tb_cart (cart_id, guest_id, resortid, resort_room_id, checkindate, checkoutdate, num_adults, num_kids, cart_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $insert->execute([$nextCartId, $guestId, $resortid, $resort_room_id, $checkindate, $checkoutdate, $num_adults, $num_kids]);
    } else {
        if(!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        
        $cartItem = [
            'cart_id' => 'temp_' . uniqid(),
            'resortid' => $resortid,
            'resort_room_id' => $resort_room_id,
            'checkindate' => $checkindate,
            'checkoutdate' => $checkoutdate,
            'num_adults' => $num_adults,
            'num_kids' => $num_kids,
            'total_amount' => $total_amount,
        ];
        
        $_SESSION['guest_cart'][] = $cartItem;
    }
    header('Location: index.php?page=cart&added=1');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    $cartId = (int)$_POST['cart_id'];
    $checkindate = $_POST['checkindate'];
    $checkoutdate = $_POST['checkoutdate'];
    $num_adults = (int)$_POST['num_adults'];
    $num_kids = (int)$_POST['num_kids'];
    $resort_room_id = !empty($_POST['resort_room_id']) ? (int)$_POST['resort_room_id'] : 0;
    
    if($isLoggedIn) {
        $update = $conn->prepare("UPDATE tb_cart SET checkindate = ?, checkoutdate = ?, num_adults = ?, num_kids = ?, resort_room_id = ? WHERE cart_id = ? AND guest_id = ?");
        $update->execute([$checkindate, $checkoutdate, $num_adults, $num_kids, $resort_room_id, $cartId, $guestId]);
    } else {
        foreach($_SESSION['guest_cart'] as &$item) {
            if($item['cart_id'] == $cartId || $item['cart_id'] == 'temp_' . $cartId) {
                $item['checkindate'] = $checkindate;
                $item['checkoutdate'] = $checkoutdate;
                $item['num_adults'] = $num_adults;
                $item['num_kids'] = $num_kids;
                $item['resort_room_id'] = $resort_room_id;
                break;
            }
        }
    }
    header('Location: index.php?page=cart');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_cart'])) {
    $cartId = $_POST['cart_id'];
    
    if($isLoggedIn) {
        $remove = $conn->prepare("DELETE FROM tb_cart WHERE cart_id = ? AND guest_id = ? AND (cart_status = 'Pending' OR cart_status = '' OR cart_status IS NULL)");
        $remove->execute([$cartId, $guestId]);
    } else {
        foreach($_SESSION['guest_cart'] as $key => $item) {
            if($item['cart_id'] == $cartId || $item['cart_id'] == 'temp_' . $cartId) {
                unset($_SESSION['guest_cart'][$key]);
                $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']);
                break;
            }
        }
    }
    header('Location: index.php?page=cart');
    exit;
}

$items = [];
$cartTotal = 0;

if($isLoggedIn) {
    $cartItems = $conn->prepare("
        SELECT c.*, r.resortname, r.resortaddress, r.adultEntranceFee, r.kidsEntranceFee, r.mun, r.district,
               rr.room_name, rr.room_price, rr.room_capacity
        FROM tb_cart c 
        JOIN tb_resort r ON c.resortid = r.resortid 
        LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
        WHERE c.guest_id = ? AND (c.cart_status = 'Pending' OR c.cart_status = '' OR c.cart_status IS NULL)
        ORDER BY c.cart_id DESC
    ");
    $cartItems->execute([$guestId]);
    
    while($item = $cartItems->fetch()) {
        $checkin = new DateTime($item['checkindate']);
        $checkout = new DateTime($item['checkoutdate']);
        $numDays = $checkout->diff($checkin)->days;
        
        $adultFee = $item['adultEntranceFee'] * ($item['num_adults'] ?? 0);
        $kidsFee = $item['kidsEntranceFee'] * ($item['num_kids'] ?? 0);
        $roomFee = ($item['room_price'] ?? 0) * max(1, $numDays);
        $item['item_total'] = $adultFee + $kidsFee + $roomFee;
        $item['num_days'] = max(1, $numDays);
        $cartTotal += $item['item_total'];
        $items[] = $item;
    }
} else {
    if(!isset($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }
    
    foreach($_SESSION['guest_cart'] as $sessionItem) {
        $resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
        $resort->execute([$sessionItem['resortid']]);
        $resortData = $resort->fetch();
        
        if($resortData) {
            $roomData = null;
            if($sessionItem['resort_room_id']) {
                $room = $conn->prepare("SELECT * FROM tb_resort_room WHERE resort_room_id = ?");
                $room->execute([$sessionItem['resort_room_id']]);
                $roomData = $room->fetch();
            }
            
            $checkin = new DateTime($sessionItem['checkindate']);
            $checkout = new DateTime($sessionItem['checkoutdate']);
            $numDays = $checkout->diff($checkin)->days;
            
            $adultFee = $resortData['adultEntranceFee'] * ($sessionItem['num_adults'] ?? 0);
            $kidsFee = $resortData['kidsEntranceFee'] * ($sessionItem['num_kids'] ?? 0);
            $roomFee = ($roomData['room_price'] ?? 0) * max(1, $numDays);
            $itemTotal = $adultFee + $kidsFee + $roomFee;
            
            $item = $sessionItem;
            $item['resortname'] = $resortData['resortname'];
            $item['resortaddress'] = $resortData['resortaddress'];
            $item['mun'] = $resortData['mun'];
            $item['adultEntranceFee'] = $resortData['adultEntranceFee'];
            $item['kidsEntranceFee'] = $resortData['kidsEntranceFee'];
            $item['room_name'] = $roomData['room_name'] ?? null;
            $item['room_price'] = $roomData['room_price'] ?? 0;
            $item['room_capacity'] = $roomData['room_capacity'] ?? null;
            $item['item_total'] = $itemTotal;
            
            $items[] = $item;
            $cartTotal += $itemTotal;
        }
    }
}
?>

<div class="p-6">
    <?php if(isset($_GET['added'])): ?>
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        <span>Item added to cart successfully!</span>
    </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Bookings</h1>
            <p class="text-gray-500">Manage your booking selections</p>
        </div>
        <?php if(count($items) > 0): ?>
        <div class="text-xl font-bold text-purple-600">
            Total: ₱<?php echo number_format($cartTotal, 2); ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if(count($items) > 0): ?>
    <div class="space-y-4">
        <?php foreach($items as $item): 
            $img = $conn->prepare("SELECT file_name FROM images WHERE resortid = ? LIMIT 1");
            $img->execute([$item['resortid']]);
            $image = $img->fetch();

            $roomImgs = [];
            if(!empty($item['resort_room_id'])) {
                $roomImg = $conn->prepare("SELECT file_name FROM images WHERE resort_room_id = ?");
                $roomImg->execute([$item['resort_room_id']]);
                $roomImgs = $roomImg->fetchAll();
            }
            
            $checkin = new DateTime($item['checkindate']);
            $checkout = new DateTime($item['checkoutdate']);
            $numDays = $checkout->diff($checkin)->days;
            $numDays = max(1, $numDays);
            
            $adultFee = $item['adultEntranceFee'] * ($item['num_adults'] ?? 0);
            $kidsFee = $item['kidsEntranceFee'] * ($item['num_kids'] ?? 0);
            $roomFee = ($item['room_price'] ?? 0) * $numDays;
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <!-- Images -->
                <div class="md:w-48 h-40 md:h-auto bg-gray-100 flex-shrink-0 relative group">
                    <?php if(!empty($roomImgs)): 
                        $imgIndex = 0;
                        foreach($roomImgs as $ri):
                    ?>
                    <img src="uploads_flow/<?php echo htmlspecialchars($ri['file_name']); ?>" class="w-full h-full object-cover cursor-pointer <?php echo $imgIndex > 0 ? 'hidden' : ''; ?> cart-room-img" data-room-img-index="<?php echo $imgIndex; ?>" data-cart-room-id="<?php echo $item['cart_id']; ?>">
                    <?php 
                            $imgIndex++;
                        endforeach;
                    elseif($image['file_name']): ?>
                    <img src="uploads_flow/<?php echo htmlspecialchars($image['file_name']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-r from-purple-500 to-emerald-500 flex items-center justify-center">
                        <i class="bi bi-water-wave text-white text-3xl"></i>
                    </div>
                    <?php endif; ?>
                    <?php if(count($roomImgs) > 1): ?>
                    <button type="button" class="cart-img-prev absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition" data-cart-room-id="<?php echo $item['cart_id']; ?>"><i class="bi bi-chevron-left text-xs"></i></button>
                    <button type="button" class="cart-img-next absolute right-1 top-1/2 -translate-y-1/2 w-7 h-7 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition" data-cart-room-id="<?php echo $item['cart_id']; ?>"><i class="bi bi-chevron-right text-xs"></i></button>
                    <div class="absolute bottom-1 right-1 bg-black/50 text-white text-xs px-2 py-0.5 rounded"><?php echo count($roomImgs); ?> photos</div>
                    <?php endif; ?>
                </div>
                
                <!-- Details -->
                <div class="flex-1 p-5">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-gray-800"><?php echo htmlspecialchars($item['resortname']); ?></h3>
                            <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($item['resortaddress']); ?>, <?php echo htmlspecialchars($item['mun']); ?>
                            </p>
                            
                            <div class="flex flex-wrap gap-4 mt-3 text-sm">
                                <div>
                                    <span class="text-gray-400">Check-in:</span>
                                    <span class="font-medium text-gray-700"><?php echo date('M j, Y', strtotime($item['checkindate'])); ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Check-out:</span>
                                    <span class="font-medium text-gray-700"><?php echo date('M j, Y', strtotime($item['checkoutdate'])); ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Guests:</span>
                                    <span class="font-medium text-gray-700"><?php echo $item['num_adults']; ?> Adult, <?php echo $item['num_kids']; ?> Kid</span>
                                </div>
                            </div>
                            
                            <?php if($item['room_name']): ?>
                            <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-purple-50 rounded-lg text-sm">
                                <i class="bi bi-door-open text-purple-500"></i>
                                <span class="text-purple-700"><?php echo htmlspecialchars($item['room_name']); ?> - ₱<?php echo number_format($item['room_price'], 0); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-2xl font-bold text-purple-600">₱<?php echo number_format($item['item_total'], 2); ?></p>
                            <p class="text-xs text-gray-400 mt-1">
                                Room: ₱<?php echo number_format($roomFee, 2); ?><br>
                                Adult: ₱<?php echo number_format($adultFee, 2); ?><br>
                                Kids: ₱<?php echo number_format($kidsFee, 2); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100 flex gap-3">
                        <button onclick="editCart(<?php echo $item['cart_id']; ?>, '<?php echo $item['checkindate']; ?>', '<?php echo $item['checkoutdate']; ?>', <?php echo $item['num_adults']; ?>, <?php echo $item['num_kids']; ?>, <?php echo $item['resortid']; ?>, <?php echo $item['resort_room_id'] ?? 0; ?>)" class="px-4 py-2 border border-purple-200 text-purple-600 rounded-lg text-sm font-medium hover:bg-purple-50 transition">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button onclick="removeFromCart(<?php echo $item['cart_id']; ?>)" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <a href="?page=resorts" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left"></i> Continue Browsing
        </a>
        <?php if($isLoggedIn): ?>
        <button onclick="checkoutCart()" class="px-8 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
            Proceed to Checkout (₱<?php echo number_format($cartTotal, 2); ?>)
        </button>
        <?php else: ?>
        <a href="login/guest-login.php?redirect=checkout" class="px-8 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
            Login to Checkout (₱<?php echo number_format($cartTotal, 2); ?>)
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-cart-x text-gray-400 text-3xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Your booking is empty</h2>
        <p class="text-gray-500 mb-6">Browse resorts and add your bookings</p>
        <a href="?page=resorts" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
            <i class="bi bi-compass"></i> Browse Resorts
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Cart Modal -->
<div id="editCartModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Edit Booking</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="update_cart" value="1">
            <input type="hidden" name="cart_id" id="editCartId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                <input type="date" name="checkindate" id="editCheckin" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                <input type="date" name="checkoutdate" id="editCheckout" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                <select name="resort_room_id" id="editRoom" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl">
                    <option value="">No room</option>
                </select>
                <div id="editRoomPreview" class="mt-2 hidden">
                    <div class="relative group">
                        <img id="editRoomPreviewImg" class="w-full h-32 object-cover rounded-xl cursor-pointer">
                        <button id="editPrevImageBtn" type="button" class="absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hidden"><i class="bi bi-chevron-left text-xs"></i></button>
                        <button id="editNextImageBtn" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 w-7 h-7 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hidden"><i class="bi bi-chevron-right text-xs"></i></button>
                        <div id="editImageCounter" class="absolute bottom-1 right-1 bg-black/50 text-white text-xs px-2 py-0.5 rounded hidden"></div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adults</label>
                    <input type="number" name="num_adults" id="editAdults" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kids</label>
                    <input type="number" name="num_kids" id="editKids" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" min="0">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Checkout</h3>
        </div>
        <form id="checkoutForm" method="POST" action="index.php?page=checkout" class="p-6">
            <input type="hidden" name="cart_id" id="checkoutCartId">
            <input type="hidden" name="resortid" id="checkoutResortId">
            <input type="hidden" name="resort_room_id" id="checkoutRoomId">
            <input type="hidden" name="checkindate" id="checkoutCheckin">
            <input type="hidden" name="checkoutdate" id="checkoutCheckout">
            <input type="hidden" name="num_adults" id="checkoutAdults">
            <input type="hidden" name="num_kids" id="checkoutKids">
            <input type="hidden" name="total_amount" id="checkoutTotal">
            
            <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                <p class="text-sm text-gray-500 mb-1">Total Amount</p>
                <p class="text-3xl font-bold text-purple-600" id="checkoutTotalDisplay">₱0.00</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                        <input type="radio" name="payment_method" value="Cash on Arrival" checked class="w-4 h-4 text-purple-600">
                        <span class="font-medium text-gray-700">Cash on Arrival</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                        <input type="radio" name="payment_method" value="GCash" class="w-4 h-4 text-purple-600">
                        <span class="font-medium text-gray-700">GCash</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                <textarea name="message" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" rows="2" placeholder="Any special requests?"></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeCheckoutModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">Place Order</button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox -->
<div id="imageLightbox" class="fixed inset-0 bg-black/80 z-[100] hidden flex items-center justify-center">
    <button id="lightboxClose" type="button" class="absolute top-4 right-4 w-10 h-10 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-xl z-10"><i class="bi bi-x-lg"></i></button>
    <button id="lightboxPrev" type="button" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-2xl z-10 hidden"><i class="bi bi-chevron-left"></i></button>
    <button id="lightboxNext" type="button" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-2xl z-10 hidden"><i class="bi bi-chevron-right"></i></button>
    <div id="lightboxCounter" class="absolute top-4 left-4 bg-black/40 text-white text-sm px-3 py-1 rounded-full z-10 hidden"></div>
    <img id="lightboxImg" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg">
</div>

<script>
function removeFromCart(cartId) {
    if(!confirm('Remove this item from cart?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?page=cart';
    
    const input1 = document.createElement('input');
    input1.type = 'hidden';
    input1.name = 'remove_from_cart';
    input1.value = '1';
    form.appendChild(input1);
    
    const input2 = document.createElement('input');
    input2.type = 'hidden';
    input2.name = 'cart_id';
    input2.value = cartId;
    form.appendChild(input2);
    
    document.body.appendChild(form);
    form.submit();
}

function editCart(cartId, checkin, checkout, adults, kids, resortId, roomId) {
    document.getElementById('editCartId').value = cartId;
    document.getElementById('editCheckin').value = checkin;
    document.getElementById('editCheckin').min = new Date().toISOString().split('T')[0];
    document.getElementById('editCheckout').value = checkout;
    document.getElementById('editCheckout').min = checkin;
    document.getElementById('editAdults').value = adults;
    document.getElementById('editKids').value = kids;
    
    // When checkin changes, update checkout min
    document.getElementById('editCheckin').addEventListener('change', function() {
        document.getElementById('editCheckout').min = this.value;
    });
    
    // Load rooms for this resort
    const roomSelect = document.getElementById('editRoom');
    roomSelect.innerHTML = '<option value="">No room</option>';
    
    $.getJSON('api/get-resort-rooms.php', {resortid: resortId}, function(data) {
        if(data.data) {
            data.data.forEach(function(room) {
                const option = document.createElement('option');
                option.value = room.resort_room_id;
                option.textContent = room.room_name + ' - ₱' + parseInt(room.room_price).toLocaleString();
                if(room.images && room.images.length) option.dataset.images = JSON.stringify(room.images);
                if(room.resort_room_id == roomId) option.selected = true;
                roomSelect.appendChild(option);
            });
            // Trigger preview for initially selected room
            $(roomSelect).trigger('change');
        }
    });
    
    document.getElementById('editCartModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editCartModal').classList.add('hidden');
}

function checkoutCart() {
    const items = <?php echo json_encode($items); ?>;
    if(items.length === 0) return;
    
    if(items.length === 1) {
        const item = items[0];
        document.getElementById('checkoutCartId').value = item.cart_id;
        document.getElementById('checkoutResortId').value = item.resortid;
        document.getElementById('checkoutRoomId').value = item.resort_room_id || 0;
        document.getElementById('checkoutCheckin').value = item.checkindate;
        document.getElementById('checkoutCheckout').value = item.checkoutdate;
        document.getElementById('checkoutAdults').value = item.num_adults;
        document.getElementById('checkoutKids').value = item.num_kids;
        document.getElementById('checkoutTotal').value = item.item_total;
        document.getElementById('checkoutTotalDisplay').textContent = '₱' + parseFloat(item.item_total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('checkoutModal').classList.remove('hidden');
    } else {
        alert('Multi-item checkout is not yet supported. Please checkout items one at a time.');
    }
}

function closeCheckoutModal() {
    document.getElementById('checkoutModal').classList.add('hidden');
}

// Close modals on backdrop click
document.getElementById('editCartModal').addEventListener('click', function(e) {
    if(e.target === this) closeEditModal();
});
document.getElementById('checkoutModal').addEventListener('click', function(e) {
    if(e.target === this) closeCheckoutModal();
});

// Edit modal room image gallery
let editImageIndex = 0;
let editImages = [];

function showEditImage(index) {
    if(!editImages.length) return;
    const img = $('#editRoomPreviewImg');
    const counter = $('#editImageCounter');
    const prevBtn = $('#editPrevImageBtn');
    const nextBtn = $('#editNextImageBtn');
    img.attr('src', 'uploads_flow/' + editImages[index]);
    counter.text((index + 1) + '/' + editImages.length);
    prevBtn.toggle(index > 0);
    nextBtn.toggle(index < editImages.length - 1);
}

$('#editPrevImageBtn').on('click', function() {
    if(editImageIndex > 0) { editImageIndex--; showEditImage(editImageIndex); }
});

$('#editNextImageBtn').on('click', function() {
    if(editImageIndex < editImages.length - 1) { editImageIndex++; showEditImage(editImageIndex); }
});

$('#editRoom').on('change', function() {
    const selected = $(this).find('option:selected');
    const preview = $('#editRoomPreview');
    const counter = $('#editImageCounter');
    editImageIndex = 0;

    try {
        editImages = selected.data('images') || [];
    } catch(e) {
        editImages = [];
    }

    if(editImages.length > 0) {
        showEditImage(0);
        counter.toggleClass('hidden', editImages.length <= 1);
        preview.removeClass('hidden');
    } else {
        preview.addClass('hidden');
    }
});

// Cart item room image gallery navigation
$(document).on('click', '.cart-img-prev', function() {
    const container = $(this).closest('.relative');
    const imgs = container.find('.cart-room-img');
    const current = imgs.filter(':not(.hidden)');
    const idx = parseInt(current.data('room-img-index'));
    const prev = (idx - 1 + imgs.length) % imgs.length;
    current.addClass('hidden');
    imgs.eq(prev).removeClass('hidden');
});

$(document).on('click', '.cart-img-next', function() {
    const container = $(this).closest('.relative');
    const imgs = container.find('.cart-room-img');
    const current = imgs.filter(':not(.hidden)');
    const idx = parseInt(current.data('room-img-index'));
    const next = (idx + 1) % imgs.length;
    current.addClass('hidden');
    imgs.eq(next).removeClass('hidden');
});

// Lightbox
let lightboxImages = [];
let lightboxIndex = 0;

function openLightbox(images, index) {
    lightboxImages = images;
    lightboxIndex = index;
    const lb = $('#imageLightbox');
    const img = $('#lightboxImg');
    const prev = $('#lightboxPrev');
    const next = $('#lightboxNext');
    const counter = $('#lightboxCounter');
    if(!images.length) return;
    img.attr('src', 'uploads_flow/' + images[index]);
    prev.toggle(images.length > 1 && index > 0);
    next.toggle(images.length > 1 && index < images.length - 1);
    counter.text((index + 1) + '/' + images.length).toggleClass('hidden', images.length <= 1);
    lb.removeClass('hidden');
    document.body.style.overflow = 'hidden';
}

// Cart item images click
$(document).on('click', '.cart-room-img', function() {
    const container = $(this).closest('.relative');
    const imgs = container.find('.cart-room-img');
    const images = imgs.map(function() { return $(this).attr('src').replace('uploads_flow/', ''); }).get();
    const idx = parseInt($(this).data('room-img-index'));
    openLightbox(images, idx);
});

// Edit modal image click
$('#editRoomPreviewImg').on('click', function() {
    if(editImages.length > 0) openLightbox(editImages, editImageIndex);
});

$('#lightboxClose, #imageLightbox').on('click', function(e) {
    if(e.target === this || $(e.target).hasClass('bi-x-lg')) {
        $('#imageLightbox').addClass('hidden');
        document.body.style.overflow = '';
    }
});

$('#lightboxPrev').on('click', function() {
    if(lightboxIndex > 0) { lightboxIndex--; openLightbox(lightboxImages, lightboxIndex); }
});

$('#lightboxNext').on('click', function() {
    if(lightboxIndex < lightboxImages.length - 1) { lightboxIndex++; openLightbox(lightboxImages, lightboxIndex); }
});

$(document).on('keydown', function(e) {
    if(!$('#imageLightbox').hasClass('hidden')) {
        if(e.key === 'Escape') { $('#imageLightbox').addClass('hidden'); document.body.style.overflow = ''; }
        else if(e.key === 'ArrowLeft') { $('#lightboxPrev').click(); }
        else if(e.key === 'ArrowRight') { $('#lightboxNext').click(); }
    }
});
</script>

