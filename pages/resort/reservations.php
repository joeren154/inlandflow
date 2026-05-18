<?php
$resortId = $_SESSION['user_reg'];

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<script>window.location.href = "index.php?page=login";</script>';
    exit;
}

// Auto-update reservations: Change Approved to Completed if checkout date has passed
$autoUpdate = $conn->prepare("
    UPDATE tb_placed_order po
    JOIN tb_cart c ON po.cart_id = c.cart_id
    SET po.reservation_status = 'Completed'
    WHERE po.reservation_status = 'Approved'
    AND c.checkoutdate < CURDATE()
    AND c.resortid = ?
");
$autoUpdate->execute([$resortId]);
$autoUpdatedCount = $autoUpdate->rowCount();

// Update room status to Available for auto-completed reservations
if ($autoUpdatedCount > 0) {
    $updateRooms = $conn->prepare("
        UPDATE tb_resort_room rr
        JOIN tb_cart c ON rr.resort_room_id = c.resort_room_id
        JOIN tb_placed_order po ON c.cart_id = po.cart_id
        SET rr.room_status = 'Available'
        WHERE po.reservation_status = 'Completed'
        AND rr.room_status = 'Not Available'
        AND c.resortid = ?
    ");
    $updateRooms->execute([$resortId]);
}


// Handle status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $poId = (int)$_POST['po_id'];
    $status = $_POST['status'];
    $rejectReason = $_POST['reject_reason'] ?? '';
    
    $update = $conn->prepare("UPDATE tb_placed_order SET reservation_status = ?, reject_reason = ? WHERE po_id = ?");
    $update->execute([$status, $rejectReason, $poId]);
    
    // Update room availability
    if($status === 'PaymentApproval' || $status === 'Approved') {
        $getRoom = $conn->prepare("SELECT resort_room_id FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE po.po_id = ?");
        $getRoom->execute([$poId]);
        $roomId = $getRoom->fetchColumn();
        if($roomId) {
            $conn->prepare("UPDATE tb_resort_room SET room_status = 'Not Available' WHERE resort_room_id = ?")->execute([$roomId]);
        }
    } elseif($status === 'Rejected' || $status === 'Completed') {
        $getRoom = $conn->prepare("SELECT resort_room_id FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE po.po_id = ?");
        $getRoom->execute([$poId]);
        $roomId = $getRoom->fetchColumn();
        if($roomId) {
            $conn->prepare("UPDATE tb_resort_room SET room_status = 'Available' WHERE resort_room_id = ?")->execute([$roomId]);
        }
    }
    
    echo '<script>window.location.href = "index.php?page=resort-reservations&tab=' . urlencode($_GET['tab'] ?? 'Pending') . '";</script>';
    exit;
}

$activeTab = $_GET['tab'] ?? 'Pending';
$validTabs = ['Pending', 'PaymentApproval', 'Approved', 'Rejected', 'Completed', 'Reviewed'];
if(!in_array($activeTab, $validTabs)) $activeTab = 'Pending';

$search = $_GET['search'] ?? '';

// Get reservations for current tab
$sql = "
    SELECT c.*, po.po_id, po.reservation_status, po.payment_method, po.message, po.total_fee, po.reject_reason,
           po.ratings, po.rating_comment,
           g.FirstName, g.LastName, g.MiddleName, g.ContactNo, g.Address,
           r.resortname, r.adultEntranceFee, r.kidsEntranceFee,
           rr.room_name, rr.room_price
    FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id
    JOIN tb_guest g ON c.guest_id = g.guest_id
    JOIN tb_resort r ON c.resortid = r.resortid 
    LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
    WHERE c.resortid = ? AND po.reservation_status = ?
";
$params = [$resortId, $activeTab];

if($search) {
    $sql .= " AND (g.FirstName LIKE ? OR g.LastName LIKE ? OR rr.room_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY c.checkindate DESC";

$reservations = $conn->prepare($sql);
$reservations->execute($params);

// Get badge counts
$badgeCounts = [];
foreach($validTabs as $tab) {
    $cnt = $conn->prepare("SELECT COUNT(*) FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE c.resortid = ? AND po.reservation_status = ?");
    $cnt->execute([$resortId, $tab]);
    $badgeCounts[$tab] = $cnt->fetchColumn();
}
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Reservations</h1>
            <p class="text-gray-500">Manage guest reservations</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-x-auto">
        <div class="flex min-w-max">
            <?php foreach($validTabs as $tab): 
                $isActive = $tab === $activeTab;
                $badge = $badgeCounts[$tab] ?? 0;
            ?>
            <a href="?page=resort-reservations&tab=<?php echo $tab; ?>" 
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

    <!-- Search -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="resort-reservations">
            <input type="hidden" name="tab" value="<?php echo $activeTab; ?>">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search guest name or room..." class="flex-1 min-w-48 px-4 py-2 border border-gray-200 rounded-xl text-sm">
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl text-sm font-medium">Search</button>
            <?php if($search): ?>
            <a href="?page=resort-reservations&tab=<?php echo $activeTab; ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Reservations List -->
    <?php if($reservations->rowCount() > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Guest</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Room</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Dates</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Guests</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Payment</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($res = $reservations->fetch()): 
                        $guestName = ucwords(strtolower($res['FirstName'] . ' ' . ($res['MiddleName'] ? $res['MiddleName'][0] . '. ' : '') . $res['LastName']));
                        
                        // Get add-ons
                        $addOns = $conn->prepare("SELECT aod.*, rr.room_name FROM tb_add_on_details aod JOIN tb_resort_room rr ON aod.resort_room_id = rr.resort_room_id WHERE aod.po_id = ?");
                        $addOns->execute([$res['po_id']]);
                        $addOnRooms = $addOns->fetchAll();
                        
                        $addOnAmenities = $conn->prepare("SELECT aoa.*, ra.amenity_name FROM tb_add_on_amenities aoa JOIN tb_resort_amenities ra ON aoa.amenity_id = ra.amenity_id WHERE aoa.po_id = ?");
                        $addOnAmenities->execute([$res['po_id']]);
                        $amenitiesList = $addOnAmenities->fetchAll();
                        
                        $checkin_dt = new DateTime($res['checkindate']);
                        $checkout_dt = new DateTime($res['checkoutdate']);
                        $numDays = max(1, $checkout_dt->diff($checkin_dt)->days);
                        
                        $adultFee = $res['adultEntranceFee'] * $res['num_adults'];
                        $kidsFee = $res['kidsEntranceFee'] * $res['num_kids'];
                        $roomFee = ($res['room_price'] ?? 0) * $numDays;
                        $addOnTotal = 0;
                        foreach($addOnRooms as $ao) $addOnTotal += $ao['total_fee'] ?? 0;
                        $amenityTotal = 0;
                        foreach($amenitiesList as $am) $amenityTotal += $am['total_amenity_fee'] ?? 0;
                        $grandTotal = $adultFee + $kidsFee + $roomFee + $addOnTotal + $amenityTotal;
                    ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($res['FirstName'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($guestName); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($res['ContactNo']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-800"><?php echo htmlspecialchars($res['room_name'] ?: 'No room'); ?></p>
                            <?php if(count($addOnRooms) > 0): ?>
                            <p class="text-xs text-blue-600 mt-1">+<?php echo count($addOnRooms); ?> add-on(s)</p>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-800"><?php echo date('M j, Y', strtotime($res['checkindate'])); ?></p>
                            <p class="text-xs text-gray-400">to <?php echo date('M j, Y', strtotime($res['checkoutdate'])); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo $res['num_adults']; ?>A, <?php echo $res['num_kids']; ?>K</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo htmlspecialchars($res['payment_method']); ?></p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <p class="font-semibold text-gray-800">₱<?php echo number_format($grandTotal, 2); ?></p>
                        </td>
<td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <?php if($activeTab === 'Pending'): ?>
                                    <button onclick="updateStatus(<?php echo (int)$res['po_id']; ?>, 'PaymentApproval')" class="px-3 py-1.5 bg-purple-100 text-purple-600 rounded-lg text-xs font-medium hover:bg-purple-200 transition" title="Approve">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                    <button onclick="openRejectModal(<?php echo (int)$res['po_id']; ?>)" class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition" title="Reject">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                <?php elseif($activeTab === 'PaymentApproval'): ?>
                                    <button onclick="updateStatus(<?php echo (int)$res['po_id']; ?>, 'Approved')" class="px-3 py-1.5 bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium hover:bg-emerald-200 transition" title="Confirm Payment">
                                        <i class="bi bi-check-circle"></i> Confirm
                                    </button>
                                    <button onclick="openRejectModal(<?php echo (int)$res['po_id']; ?>)" class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition" title="Reject">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                <?php elseif($activeTab === 'PaymentApproval' || $activeTab === 'Approved'): ?>
                                    <button onclick="manageAddons(<?php echo (int)$res['po_id']; ?>)" class="px-3 py-1.5 bg-amber-100 text-amber-600 rounded-lg text-xs font-medium hover:bg-amber-200 transition" title="Manage Add-ons">
                                        <i class="bi bi-plus-circle"></i> Add-ons
                                    </button>
                                    <?php if($activeTab === 'Approved'): ?>
                                    <button onclick="updateStatus(<?php echo (int)$res['po_id']; ?>, 'Completed')" class="px-3 py-1.5 bg-blue-100 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-200 transition" title="Mark Completed">
                                        <i class="bi bi-check-all"></i> Complete
                                    </button>
                                    <?php endif; ?>
                                <?php elseif($activeTab === 'Rejected'): ?>
                                    <button onclick="openRejectModal(<?php echo (int)$res['po_id']; ?>)" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-200 transition" title="Update Reason">
                                        <i class="bi bi-pencil"></i> Reason
                                    </button>
                                    <button onclick="openRejectModal('<?php echo $res['po_id']; ?>)" class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition" title="Reject">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                <?php elseif($activeTab === 'PaymentApproval'): ?>
                                    <button onclick="updateStatus('<?php echo $res['po_id']; ?>', 'Approved')" class="px-3 py-1.5 bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium hover:bg-emerald-200 transition" title="Confirm Payment">
                                        <i class="bi bi-check-circle"></i> Confirm
                                    </button>
                                    <button onclick="openRejectModal('<?php echo $res['po_id']; ?>)" class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition" title="Reject">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                <?php elseif($activeTab === 'Approved'): ?>
                                    <button onclick="updateStatus('<?php echo $res['po_id']; ?>', 'Completed')" class="px-3 py-1.5 bg-blue-100 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-200 transition" title="Mark Completed">
                                        <i class="bi bi-check-all"></i> Complete
                                    </button>
                                <?php elseif($activeTab === 'Rejected'): ?>
                                    <button onclick="openRejectModal('<?php echo $res['po_id']; ?>)" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-200 transition" title="Update Reason">
                                        <i class="bi bi-pencil"></i> Reason
                                    </button>
                                <?php elseif($activeTab === 'Completed' || $activeTab === 'Reviewed'): ?>
                                    <?php if($res['ratings'] > 0): ?>
                                    <div class="flex items-center gap-0.5 text-amber-400">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill text-xs <?php echo $i <= $res['ratings'] ? '' : 'text-gray-200'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400">Not rated</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-calendar-x text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No <?php echo $activeTab; ?> reservations</h3>
        <p class="text-gray-500">There are no reservations with this status.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Reject Reservation</h3>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="po_id" id="rejectPoId">
            <input type="hidden" name="status" value="Rejected">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                <textarea name="reject_reason" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" rows="3" placeholder="Please provide a reason..." required></textarea>
            </div>
            
        <div class="flex gap-3">
            <button type="button" onclick="closeRejectModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
            <button type="submit" class="flex-1 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition">Reject</button>
        </div>
    </form>
</div>
</div>

<!-- Add-on Amenities Modal -->
<div id="addonModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Manage Add-on Amenities</h3>
            <button onclick="closeAddonModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <input type="hidden" id="addonPoId">
            
            <!-- Add New Amenity -->
            <div class="mb-6 bg-gray-50 rounded-xl p-4">
                <h4 class="font-medium text-gray-700 mb-3">Add New Amenity</h4>
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

function updateStatus(poId, status) {
    if(!confirm('Are you sure you want to ' + status + ' this reservation?')) return;
    
    $.ajax({
        url: 'api/update-reservation-status.php',
        type: 'POST',
        data: {
            po_id: poId,
            reservation_status: status
        },
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
}

function openRejectModal(poId) {
    document.getElementById('rejectPoId').value = poId;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function showToast(message) {
    document.getElementById('toastMessage').textContent = message;
    document.getElementById('toast').classList.remove('hidden');
    setTimeout(() => document.getElementById('toast').classList.add('hidden'), 3000);
}

// Add-on Amenities Management
function manageAddons(poId) {
    document.getElementById('addonPoId').value = poId;
    loadAmenities();
    loadCurrentAddons(poId);
    document.getElementById('addonModal').classList.remove('hidden');
}

function closeAddonModal() {
    document.getElementById('addonModal').classList.add('hidden');
}

function loadAmenities() {
    $.ajax({
        url: 'api/get-amenities.php',
        type: 'GET',
        data: { resortid: <?php echo $resortId; ?> },
        dataType: 'json',
        success: function(res) {
            allAmenities = res;
            let select = document.getElementById('amenitySelect');
            select.innerHTML = '<option value="">Select Amenity</option>';
            res.forEach(function(am) {
                select.innerHTML += '<option value="' + am.amenity_id + '" data-price="' + am.amenity_price + '">' + am.amenity_name + ' (₱' + parseFloat(am.amenity_price).toFixed(2) + ')</option>';
            });
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
            showToast('Amenity added! Total: ₱' + parts[1]);
            location.reload();
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
            showToast('Amenity removed! Total: ₱' + parts[1]);
            location.reload();
        } else {
            alert('Error: ' + data);
        }
    })
    .catch(err => alert('Error: ' + err));
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if(e.target === this) closeRejectModal();
});
document.getElementById('addonModal').addEventListener('click', function(e) {
    if(e.target === this) closeAddonModal();
});
</script>

