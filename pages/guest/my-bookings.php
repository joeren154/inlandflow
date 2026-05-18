<?php
$guestId = $_SESSION['user_reg'];

$guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
$guest->execute([$guestId]);
$guestData = $guest->fetch();

if (!$guestData) {
    header('Location: index.php?page=login');
    exit;
}

// Auto-update: Approved bookings with passed checkout date -> Completed
$autoUpdateStmt = $conn->prepare("
    UPDATE tb_placed_order po
    JOIN tb_cart c ON po.cart_id = c.cart_id
    SET po.reservation_status = 'Completed'
    WHERE po.reservation_status = 'Approved'
    AND c.checkoutdate < CURDATE()
    AND c.guest_id = ?
");
$autoUpdateStmt->execute([$guestId]);

// Auto-update rooms to Available for completed bookings
if ($autoUpdateStmt->rowCount() > 0) {
    $updateRooms = $conn->prepare("
        UPDATE tb_resort_room rr
        JOIN tb_cart c ON rr.resort_room_id = c.resort_room_id
        JOIN tb_placed_order po ON c.cart_id = po.cart_id
        SET rr.room_status = 'Available'
        WHERE po.reservation_status = 'Completed'
        AND c.guest_id = ?
    ");
    $updateRooms->execute([$guestId]);
}

// Handle actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['complete_booking'])) {
        $poId = (int)$_POST['po_id'];
        $update = $conn->prepare("UPDATE tb_placed_order SET reservation_status = 'Completed' WHERE po_id = ?");
        $update->execute([$poId]);
        
        $getRoom = $conn->prepare("SELECT c.resort_room_id FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE po.po_id = ? AND c.resort_room_id > 0");
        $getRoom->execute([$poId]);
        $roomId = $getRoom->fetchColumn();
        if($roomId) {
            $conn->prepare("UPDATE tb_resort_room SET room_status = 'Available' WHERE resort_room_id = ?")->execute([$roomId]);
        }
        
        $getAddOns = $conn->prepare("SELECT aod.resort_room_id FROM tb_add_on_details aod JOIN tb_placed_order po ON aod.po_id = po.po_id WHERE po.po_id = ? AND aod.resort_room_id > 0");
        $getAddOns->execute([$poId]);
        while($addOnRoom = $getAddOns->fetch()) {
            $conn->prepare("UPDATE tb_resort_room SET room_status = 'Available' WHERE resort_room_id = ?")->execute([$addOnRoom['resort_room_id']]);
        }
        
        header('Location: index.php?page=guest-bookings');
        exit;
    }
    if(isset($_POST['submit_rating'])) {
        $poId = (int)$_POST['po_id'];
        $rating = (int)$_POST['rating'];
        $comment = $_POST['rating_comment'];
        $update = $conn->prepare("UPDATE tb_placed_order SET ratings = ?, rating_comment = ?, reservation_status = 'Reviewed' WHERE po_id = ?");
        $update->execute([$rating, $comment, $poId]);
        header('Location: index.php?page=guest-bookings');
        exit;
    }
}

$activeTab = $_GET['tab'] ?? 'Pending';
$validTabs = ['Pending', 'ForPayment', 'Approved', 'Completed', 'Reviewed', 'Rejected'];
if(!in_array($activeTab, $validTabs)) $activeTab = 'Pending';

// Map tabs to statuses
$statusMap = [
    'Pending' => 'Pending',
    'ForPayment' => 'PaymentApproval',
    'Approved' => 'Approved',
    'Completed' => 'Completed',
    'Reviewed' => 'Reviewed',
    'Rejected' => 'Rejected'
];
$currentStatus = $statusMap[$activeTab];

// Get bookings for current tab
$bookings = $conn->prepare("
    SELECT c.*, po.po_id, po.reservation_status, po.payment_method, po.message, po.total_fee, po.reject_reason,
           po.ratings, po.rating_comment,
           r.resortname, r.resortaddress, r.adultEntranceFee, r.kidsEntranceFee, r.mun, r.district, r.contact_no,
           rr.room_name, rr.room_price
    FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id
    JOIN tb_resort r ON c.resortid = r.resortid 
    LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
    WHERE c.guest_id = ? AND po.reservation_status = ?
    ORDER BY c.checkindate DESC
");
$bookings->execute([$guestId, $currentStatus]);

// Get badge counts
$badgeCounts = [];
foreach($statusMap as $tab => $status) {
    $cnt = $conn->prepare("SELECT COUNT(*) FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE c.guest_id = ? AND po.reservation_status = ?");
    $cnt->execute([$guestId, $status]);
    $badgeCounts[$tab] = $cnt->fetchColumn();
}
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Bookings</h1>
            <p class="text-gray-500">View and manage your reservations</p>
        </div>
        <a href="?page=resorts" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center gap-2">
            <i class="bi bi-compass"></i> Browse Resorts
        </a>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
        <span class="text-emerald-700 font-medium">Booking confirmed successfully!</span>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-x-auto">
        <div class="flex min-w-max">
            <?php foreach($validTabs as $tab): 
                $isActive = $tab === $activeTab;
                $badge = $badgeCounts[$tab] ?? 0;
            ?>
            <a href="?page=guest-bookings&tab=<?php echo $tab; ?>" 
               class="px-5 py-4 text-sm font-medium border-b-2 transition flex items-center gap-2 whitespace-nowrap
                      <?php echo $isActive ? 'border-purple-500 text-purple-600 bg-purple-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'; ?>">
                <?php echo $tab; ?>
                <?php if($badge > 0): ?>
                <span class="px-2 py-0.5 bg-<?php echo $isActive ? 'purple' : 'gray'; ?>-100 text-<?php echo $isActive ? 'purple' : 'gray'; ?>-600 text-xs rounded-full"><?php echo $badge; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Booking List -->
    <?php if($bookings->rowCount() > 0): ?>
    <div class="space-y-4">
        <?php while($booking = $bookings->fetch()): 
            $img = $conn->prepare("SELECT file_name FROM images WHERE resortid = ? LIMIT 1");
            $img->execute([$booking['resortid']]);
            $image = $img->fetch();
            
            // Get add-on rooms
            $addOns = $conn->prepare("SELECT aod.*, rr.room_name, rr.room_price FROM tb_add_on_details aod JOIN tb_resort_room rr ON aod.resort_room_id = rr.resort_room_id WHERE aod.po_id = ?");
            $addOns->execute([$booking['po_id']]);
            $addOnRooms = $addOns->fetchAll();
            
            // Get add-on amenities
            $addOnAmenities = $conn->prepare("SELECT aoa.*, ra.amenity_name, ra.amenity_price FROM tb_add_on_amenities aoa JOIN tb_resort_amenities ra ON aoa.amenity_id = ra.amenity_id WHERE aoa.po_id = ?");
            $addOnAmenities->execute([$booking['po_id']]);
            $amenitiesList = $addOnAmenities->fetchAll();
            
            // Calculate totals
            $checkin = new DateTime($booking['checkindate']);
            $checkout = new DateTime($booking['checkoutdate']);
            $numDays = max(1, $checkout->diff($checkin)->days);
            
            $adultFee = $booking['adultEntranceFee'] * $booking['num_adults'];
            $kidsFee = $booking['kidsEntranceFee'] * $booking['num_kids'];
            $roomFee = ($booking['room_price'] ?? 0) * $numDays;
            $addOnTotal = 0;
            foreach($addOnRooms as $ao) $addOnTotal += $ao['total_fee'] ?? 0;
            $amenityTotal = 0;
            foreach($amenitiesList as $am) $amenityTotal += $am['total_amenity_fee'] ?? 0;
            $grandTotal = $adultFee + $kidsFee + $roomFee + $addOnTotal + $amenityTotal;
            
            // Status config
            $statusConfig = [
                'Pending' => ['bg' => 'bg-amber-100 text-amber-700', 'icon' => 'bi-hourglass-split'],
                'PaymentApproval' => ['bg' => 'bg-purple-100 text-purple-700', 'icon' => 'bi-credit-card'],
                'Approved' => ['bg' => 'bg-blue-100 text-blue-700', 'icon' => 'bi-check-circle'],
                'Completed' => ['bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'bi-check-circle-fill'],
                'Reviewed' => ['bg' => 'bg-teal-100 text-teal-700', 'icon' => 'bi-star-fill'],
                'Rejected' => ['bg' => 'bg-red-100 text-red-700', 'icon' => 'bi-x-circle'],
            ];
            $config = $statusConfig[$booking['reservation_status']] ?? ['bg' => 'bg-gray-100 text-gray-700', 'icon' => 'bi-circle'];
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
            <div class="flex flex-col md:flex-row">
                <!-- Image -->
                <div class="md:w-56 h-48 md:h-auto bg-gray-100 flex-shrink-0">
                    <?php if($image['file_name']): ?>
                    <img src="uploads_flow/<?php echo htmlspecialchars($image['file_name']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-r from-purple-500 to-emerald-500 flex items-center justify-center">
                        <i class="bi bi-water-wave text-white text-3xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1 p-5">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($booking['resortname']); ?></h2>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php echo $config['bg']; ?>">
                                    <i class="bi <?php echo $config['icon']; ?>"></i> <?php echo $booking['reservation_status']; ?>
                                </span>
                            </div>
                            <p class="text-gray-500 text-sm flex items-center gap-1">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($booking['resortaddress']); ?>, <?php echo htmlspecialchars($booking['mun']); ?>
                            </p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-400">Check-in</p>
                                    <p class="font-medium text-gray-700"><?php echo date('M j, Y', strtotime($booking['checkindate'])); ?></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-400">Check-out</p>
                                    <p class="font-medium text-gray-700"><?php echo date('M j, Y', strtotime($booking['checkoutdate'])); ?></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-400">Guests</p>
                                    <p class="font-medium text-gray-700"><?php echo $booking['num_adults']; ?>A, <?php echo $booking['num_kids']; ?>K</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-400">Payment</p>
                                    <p class="font-medium text-gray-700"><?php echo htmlspecialchars($booking['payment_method']); ?></p>
                                </div>
                            </div>
                            
                            <?php if($booking['room_name']): ?>
                            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 rounded-lg text-sm">
                                <i class="bi bi-door-open text-purple-500"></i>
                                <span class="text-purple-700"><?php echo htmlspecialchars($booking['room_name']); ?> - ₱<?php echo number_format($booking['room_price'], 0); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(count($addOnRooms) > 0): ?>
                            <div class="mt-2">
                                <p class="text-xs text-blue-600 font-medium mb-1">Add-on Rooms:</p>
                                <?php foreach($addOnRooms as $ao): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 rounded text-xs text-blue-700 mr-1">
                                    <?php echo htmlspecialchars($ao['room_name']); ?> - ₱<?php echo number_format($ao['room_price'], 0); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(count($amenitiesList) > 0): ?>
                            <div class="mt-2">
                                <p class="text-xs text-emerald-600 font-medium mb-1">Add-on Amenities:</p>
                                <?php foreach($amenitiesList as $am): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 rounded text-xs text-emerald-700 mr-1">
                                    <?php echo htmlspecialchars($am['amenity_name']); ?> x<?php echo $am['quantity']; ?> - ₱<?php echo number_format($am['amenity_price'], 0); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($booking['message']): ?>
                            <div class="mt-3 p-2 bg-gray-50 rounded-lg text-sm text-gray-600">
                                <span class="font-medium">Message:</span> <?php echo htmlspecialchars($booking['message']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($booking['reservation_status'] === 'Rejected' && $booking['reject_reason']): ?>
                            <div class="mt-3 p-3 bg-red-50 rounded-lg flex items-start gap-2">
                                <i class="bi bi-exclamation-triangle text-red-500 mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-red-700">Rejection Reason</p>
                                    <p class="text-sm text-red-600"><?php echo htmlspecialchars($booking['reject_reason']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-right md:text-right">
                            <p class="text-2xl font-bold text-purple-600">₱<?php echo number_format($grandTotal, 2); ?></p>
                            <p class="text-xs text-gray-400 mt-1">
                                Room: ₱<?php echo number_format($roomFee, 2); ?><br>
                                Adult: ₱<?php echo number_format($adultFee, 2); ?><br>
                                Kids: ₱<?php echo number_format($kidsFee, 2); ?><br>
                                <?php if($addOnTotal > 0): ?>Add-ons: ₱<?php echo number_format($addOnTotal, 2); ?><br><?php endif; ?>
                                <?php if($amenityTotal > 0): ?>Amenities: ₱<?php echo number_format($amenityTotal, 2); ?><br><?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                        <?php if($booking['reservation_status'] === 'PaymentApproval'): ?>
                            <button onclick="payInfo('<?php echo $booking['payment_method']; ?>', '<?php echo $booking['contact_no']; ?>')" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition">
                                <i class="bi bi-credit-card"></i> Pay Now
                            </button>
                            <button onclick="manageAddons(<?php echo $booking['po_id']; ?>)" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                                <i class="bi bi-plus-circle"></i> Add Amenities
                            </button>
                        <?php endif; ?>
                        
                        <?php if($booking['reservation_status'] === 'Approved'): ?>
                            <button onclick="manageAddons(<?php echo $booking['po_id']; ?>)" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                                <i class="bi bi-plus-circle"></i> Add Amenities
                            </button>
                        <?php endif; ?>
                        
                        <?php if($booking['reservation_status'] === 'Completed'): ?>
                            <button onclick="openRateModal(<?php echo $booking['po_id']; ?>)" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600 transition" data-resort="<?php echo htmlspecialchars($booking['resortname']); ?>">
                                <i class="bi bi-star"></i> Rate & Review
                            </button>
                        <?php endif; ?>
                        
                        <?php if($booking['reservation_status'] === 'Reviewed'): ?>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1 text-amber-500">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star-fill <?php echo $i <= $booking['ratings'] ? '' : 'text-gray-200'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-sm text-gray-600 ml-1">(<?php echo $booking['ratings']; ?>/5)</span>
                                </div>
                                <a href="index.php?page=receipt&po_id=<?php echo $booking['po_id']; ?>" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition">
                                    <i class="bi bi-receipt"></i> E-Receipt
                                </a>
                            </div>
                            <?php if($booking['rating_comment']): ?>
                            <p class="text-sm text-gray-500 w-full mt-1">"<?php echo htmlspecialchars($booking['rating_comment']); ?>"</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-calendar-x text-gray-400 text-3xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">No <?php echo $activeTab; ?> bookings</h2>
        <p class="text-gray-500 mb-6">You don't have any <?php echo strtolower($activeTab); ?> reservations.</p>
        <a href="?page=resorts" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
            <i class="bi bi-compass"></i> Browse Resorts
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Rate Modal -->
<div id="rateModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Rate & Review</h3>
            <button onclick="closeRateModal()" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="submit_rating" value="1">
            <input type="hidden" name="po_id" id="ratePoId">
            
            <div class="mb-4">
                <p class="text-sm text-gray-500 text-center mb-3"><span id="rateResortName" class="font-semibold text-gray-800"></span></p>
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating</label>
                <div class="flex gap-2 justify-center">
                    <button type="button" onclick="setRating(1)" class="rate-star text-3xl text-gray-200 hover:text-amber-400 transition" data-value="1"><i class="bi bi-star-fill"></i></button>
                    <button type="button" onclick="setRating(2)" class="rate-star text-3xl text-gray-200 hover:text-amber-400 transition" data-value="2"><i class="bi bi-star-fill"></i></button>
                    <button type="button" onclick="setRating(3)" class="rate-star text-3xl text-gray-200 hover:text-amber-400 transition" data-value="3"><i class="bi bi-star-fill"></i></button>
                    <button type="button" onclick="setRating(4)" class="rate-star text-3xl text-gray-200 hover:text-amber-400 transition" data-value="4"><i class="bi bi-star-fill"></i></button>
                    <button type="button" onclick="setRating(5)" class="rate-star text-3xl text-gray-200 hover:text-amber-400 transition" data-value="5"><i class="bi bi-star-fill"></i></button>
                </div>
                <p class="text-center text-sm text-gray-500 mt-1" id="ratingText">Excellent!</p>
                <input type="hidden" name="rating" id="ratingValue" value="5">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback</label>
                <textarea name="rating_comment" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" rows="3" placeholder="Tell others about your experience..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeRateModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl font-medium hover:shadow-lg transition">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<!-- Add-on Amenities Modal -->
<div id="addonModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Add Amenities to Booking</h3>
            <button onclick="closeAddonModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <input type="hidden" id="addonPoId">

            <!-- Add New Amenity -->
            <div class="mb-6 bg-gray-50 rounded-xl p-4">
                <h4 class="font-medium text-gray-700 mb-3">Add Amenity</h4>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <select id="amenitySelect" class="col-span-1 px-3 py-2 border border-gray-200 rounded-xl text-sm">
                        <option value="">Select Amenity</option>
                    </select>
                    <input type="number" id="amenityQty" value="1" min="1" class="px-3 py-2 border border-gray-200 rounded-xl text-sm" placeholder="Qty">
                    <button onclick="addAddonAmenity()" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm hover:bg-purple-700 transition">
                        <i class="bi bi-plus"></i> Add
                    </button>
                </div>
            </div>

            <!-- Current Add-ons -->
            <div>
                <h4 class="font-medium text-gray-700 mb-3">Current Add-on Amenities</h4>
                <div id="addonList" class="space-y-2">
                    <!-- Dynamically populated -->
                </div>
                <div id="noAddons" class="text-center py-8 text-gray-400">
                    <i class="bi bi-inbox text-3xl"></i>
                    <p class="mt-2">No add-on amenities yet</p>
                </div>
            </div>

            <!-- Total Display -->
            <div class="mt-6 pt-4 border-t border-gray-200 flex justify-between items-center">
                <span class="font-semibold text-gray-700">New Total:</span>
                <span class="text-2xl font-bold text-purple-600" id="newTotal">₱0.00</span>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2">
        <i class="bi bi-check-circle"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<script>
let allAmenities = [];
let currentBookingTotal = 0;

function payInfo(method, contact) {
    if(method === 'GCash') {
        alert('Please pay via GCash to this number: ' + contact);
    } else {
        alert('Please pay upon arrival at the resort. Contact: ' + contact);
    }
}

function openRateModal(poId) {
    var button = event.target.closest('button');
    var resortName = button ? button.dataset.resort : '';
    document.getElementById('ratePoId').value = poId;
    document.getElementById('rateResortName').textContent = resortName;
    setRating(5);
    document.getElementById('rateModal').classList.remove('hidden');
}

function closeRateModal() {
    document.getElementById('rateModal').classList.add('hidden');
}

function setRating(value) {
    document.getElementById('ratingValue').value = value;
    var texts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    document.getElementById('ratingText').textContent = texts[value];
    document.querySelectorAll('.rate-star').forEach(function(star) {
        var starVal = parseInt(star.dataset.value);
        if(starVal <= value) {
            star.classList.remove('text-gray-200');
            star.classList.add('text-amber-400');
        } else {
            star.classList.remove('text-amber-400');
            star.classList.add('text-gray-200');
        }
    });
}

// Add-on Amenities Management
function manageAddons(poId) {
    document.getElementById('addonPoId').value = poId;
    loadAmenities(poId);
    loadCurrentAddons(poId);
    document.getElementById('addonModal').classList.remove('hidden');
}

function closeAddonModal() {
    document.getElementById('addonModal').classList.add('hidden');
}

function loadAmenities(poId) {
    $.ajax({
        url: 'api/get-order-details.php',
        type: 'GET',
        data: { po_id: poId },
        dataType: 'json',
        success: function(res) {
            if(res && res.resortid) {
                $.ajax({
                    url: 'api/get-amenities.php',
                    type: 'GET',
                    data: { resortid: res.resortid },
                    dataType: 'json',
                    success: function(amenities) {
                        allAmenities = amenities;
                        let select = document.getElementById('amenitySelect');
                        select.innerHTML = '<option value="">Select Amenity</option>';
                        if(amenities && amenities.length > 0) {
                            amenities.forEach(function(am) {
                                select.innerHTML += '<option value="' + am.amenity_id + '" data-price="' + am.amenity_price + '">' + am.amenity_name + ' (₱' + parseFloat(am.amenity_price).toFixed(2) + ')</option>';
                            });
                        } else {
                            select.innerHTML = '<option value="">No amenities available</option>';
                        }
                    },
                    error: function() {
                        document.getElementById('amenitySelect').innerHTML = '<option value="">Error loading amenities</option>';
                    }
                });
            } else {
                document.getElementById('amenitySelect').innerHTML = '<option value="">No booking found</option>';
            }
        },
        error: function() {
            document.getElementById('amenitySelect').innerHTML = '<option value="">Error loading booking</option>';
        }
    });
}

function loadCurrentAddons(poId) {
    $.ajax({
        url: 'api/get-order-addons.php',
        type: 'GET',
        data: { po_id: poId },
        dataType: 'json',
        success: function(res) {
            let html = '';
            let hasAddons = false;
            let total = currentBookingTotal;

            if(res.amenities && res.amenities.length > 0) {
                hasAddons = true;
                res.amenities.forEach(function(item) {
                    html += '<div class="flex items-center justify-between bg-white border border-gray-200 rounded-xl p-3">';
                    html += '<div>';
                    html += '<p class="font-medium text-gray-800">' + item.amenity_name + '</p>';
                    html += '<p class="text-sm text-gray-500">Qty: ' + item.quantity + ' × ₱' + parseFloat(item.amenity_price).toFixed(2) + '</p>';
                    html += '</div>';
                    html += '<div class="flex items-center gap-3">';
                    html += '<span class="font-semibold text-gray-800">₱' + parseFloat(item.total_amenity_fee).toFixed(2) + '</span>';
                    html += '<button onclick="deleteAddonAmenity(' + item.add_on_amenity_id + ')" class="text-red-500 hover:text-red-700">';
                    html += '<i class="bi bi-trash"></i>';
                    html += '</button>';
                    html += '</div>';
                    html += '</div>';
                });
            }

            document.getElementById('addonList').innerHTML = html;
            document.getElementById('noAddons').style.display = hasAddons ? 'none' : 'block';

            // Update total display
            updateTotalDisplay(poId);
        }
    });
}

function updateTotalDisplay(poId) {
    $.ajax({
        url: 'api/get-order-total.php',
        type: 'GET',
        data: { po_id: poId },
        dataType: 'json',
        success: function(res) {
            document.getElementById('newTotal').textContent = '₱' + parseFloat(res.total || 0).toFixed(2);
        }
    });
}

function addAddonAmenity() {
    const poId = document.getElementById('addonPoId').value;
    const amenitySelect = document.getElementById('amenitySelect');
    const amenityId = amenitySelect.value;
    const qty = document.getElementById('amenityQty').value;

    if(!amenityId) {
        alert('Please select an amenity');
        return;
    }

    const selectedOption = amenitySelect.selectedOptions[0];
    const price = parseFloat(selectedOption.dataset.price) || 0;

    if(price <= 0) {
        alert('Invalid price. Please try again.');
        return;
    }

    const formData = new FormData();
    formData.append('po_id', poId);
    formData.append('amenity_id', amenityId);
    formData.append('amenity_price', price);
    formData.append('quantity', qty);

    fetch('api/add-add-on-amenity-details.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if(data.startsWith('OK')) {
            const parts = data.split('|');
            showToast('Amenity added! New Total: ₱' + parts[1]);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + data);
        }
    })
    .catch(err => alert('Error: ' + err));
}

function deleteAddonAmenity(addOnAmenityId) {
    if(!confirm('Remove this amenity?')) return;

    const poId = document.getElementById('addonPoId').value;

    const formData = new FormData();
    formData.append('add_on_amenity_id', addOnAmenityId);

    fetch('api/delete-add-ons-amenity.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if(data.startsWith('OK')) {
            const parts = data.split('|');
            showToast('Amenity removed! New Total: ₱' + parts[1]);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + data);
        }
    })
    .catch(err => alert('Error: ' + err));
}

function showToast(message) {
    document.getElementById('toastMessage').textContent = message;
    document.getElementById('toast').classList.remove('hidden');
    setTimeout(() => document.getElementById('toast').classList.add('hidden'), 3000);
}

document.getElementById('rateModal').addEventListener('click', function(e) {
    if(e.target === this) closeRateModal();
});
document.getElementById('addonModal').addEventListener('click', function(e) {
    if(e.target === this) closeAddonModal();
});
</script>

