<?php
$resortId = $_SESSION['user_reg'];

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<script>window.location.href = "index.php?page=login";</script>';
    exit;
}

// Handle room delete via POST for proper AJAX
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_room'])) {
    $roomId = (int)($_POST['room_id'] ?? 0);

    $imgs = $conn->prepare("SELECT file_name FROM images WHERE resort_room_id = ? AND resortid = ?");
    $imgs->execute([$roomId, $resortId]);
    foreach($imgs as $img) {
        $fp = 'uploads_flow/' . $img['file_name'];
        if(file_exists($fp)) unlink($fp);
    }
    $conn->prepare("DELETE FROM images WHERE resort_room_id = ? AND resortid = ?")->execute([$roomId, $resortId]);

    $delete = $conn->prepare("DELETE FROM tb_resort_room WHERE resort_room_id = ? AND resortid = ?");
    $delete->execute([$roomId, $resortId]);

    if(isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Room deleted successfully!']);
        exit;
    }
}

// Get entrance fees
$adultFee = $resortData['adultEntranceFee'] ?? 0;
$kidsFee = $resortData['kidsEntranceFee'] ?? 0;

// Get all rooms with current availability
$checkDateIn = $_GET['checkin'] ?? date('Y-m-d');
$checkDateOut = $_GET['checkout'] ?? date('Y-m-d', strtotime('+1 day'));

$rooms = $conn->prepare("
    SELECT rr.*,
        (SELECT COUNT(*) FROM tb_cart c 
         JOIN tb_placed_order po ON c.cart_id = po.cart_id 
         WHERE c.resort_room_id = rr.resort_room_id 
           AND po.reservation_status IN ('Pending', 'PaymentApproval', 'Approved')
           AND c.checkindate <= ? AND c.checkoutdate >= ?) as active_bookings
    FROM tb_resort_room rr 
    WHERE rr.resortid = ? 
    ORDER BY rr.resort_room_id
");
$rooms->execute([$checkDateOut, $checkDateIn, $resortId]);

// Get amenities
$amenities = $conn->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ? ORDER BY amenity_id DESC");
$amenities->execute([$resortId]);

// Handle amenity add
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_amenity'])) {
    $name = $_POST['amenity_name'] ?? '';
    $price = (float)($_POST['amenity_price'] ?? 0);
    if($name) {
        $insert = $conn->prepare("INSERT INTO tb_resort_amenities (resortid, amenity_name, amenity_price) VALUES (?, ?, ?)");
        $insert->execute([$resortId, $name, $price]);
    }
}

if(isset($_GET['delete_amenity'])) {
    $amId = (int)$_GET['delete_amenity'];
    $conn->prepare("DELETE FROM tb_resort_amenities WHERE amenity_id = ? AND resortid = ?")->execute([$amId, $resortId]);
    echo '<script>window.location.href = "$1";</script>';
    exit;
}
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Accommodations & Facilities</h1>
            <p class="text-gray-500">Manage rooms, entrance fees, and amenities</p>
        </div>
    </div>

    <!-- Entrance Fees Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="bi bi-ticket text-purple-500"></i> Entrance Fees</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-purple-50 rounded-xl p-4">
                    <p class="text-sm text-purple-600 mb-1">Adult</p>
                    <p class="text-3xl font-bold text-gray-800">₱<?php echo number_format($adultFee, 0); ?></p>
                    <button onclick="editFee('adult', <?php echo $adultFee; ?>)" class="text-xs text-purple-600 hover:text-purple-700 mt-2">Change</button>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4">
                    <p class="text-sm text-emerald-600 mb-1">Kids</p>
                    <p class="text-3xl font-bold text-gray-800">₱<?php echo number_format($kidsFee, 0); ?></p>
                    <button onclick="editFee('kids', <?php echo $kidsFee; ?>)" class="text-xs text-emerald-600 hover:text-emerald-700 mt-2">Change</button>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="bi bi-search text-blue-500"></i> Check Availability</h2>
            <form method="GET" class="space-y-3">
                <input type="hidden" name="page" value="resort-rooms">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Check-in</label>
                        <input type="date" name="checkin" value="<?php echo $checkDateIn; ?>" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Check-out</label>
                        <input type="date" name="checkout" value="<?php echo $checkDateOut; ?>" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl text-sm font-medium hover:shadow-lg transition">Check Availability</button>
            </form>
        </div>
    </div>

    <!-- Rooms Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Rooms & Cottages</h2>
            <button onclick="openRoomModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Add Room
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($room = $rooms->fetch()): 
                $isAvailable = $room['room_status'] === 'Available' && $room['active_bookings'] == 0;
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                <div class="bg-gradient-to-r <?php echo $isAvailable ? 'from-purple-500 to-purple-600' : 'from-gray-400 to-gray-500'; ?> p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-white"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $isAvailable ? 'bg-white/20 text-white' : 'bg-red-500 text-white'; ?>">
                            <?php echo $isAvailable ? 'Available' : ($room['active_bookings'] > 0 ? 'Booked' : $room['room_status']); ?>
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <?php if (!empty($room['room_description'])): ?>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($room['room_description']); ?></div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Capacity</span>
                            <span class="font-semibold text-gray-700"><?php echo $room['room_capacity']; ?> persons</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Price</span>
                            <span class="text-2xl font-bold text-purple-600">₱<?php echo number_format($room['room_price'], 0); ?></span>
                        </div>
                        <?php if($room['active_bookings'] > 0): ?>
                        <div class="flex justify-between text-red-600">
                            <span class="text-sm"><i class="bi bi-calendar-check"></i> Active Bookings</span>
                            <span class="font-semibold"><?php echo $room['active_bookings']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                        <div class="mt-5 pt-4 border-t border-gray-100 flex gap-2">
                            <button onclick="editRoom(<?php echo $room['resort_room_id']; ?>, this.dataset)" data-room-name="<?php echo htmlspecialchars($room['room_name'], ENT_QUOTES); ?>" data-room-description="<?php echo htmlspecialchars($room['room_description'] ?? '', ENT_QUOTES); ?>" data-room-capacity="<?php echo $room['room_capacity']; ?>" data-room-price="<?php echo $room['room_price']; ?>" data-room-status="<?php echo $room['room_status']; ?>" class="flex-1 px-3 py-2 border border-purple-500 text-purple-600 rounded-lg text-sm font-medium hover:bg-purple-50 transition">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button data-room-id="<?php echo $room['resort_room_id']; ?>" data-room-name="<?php echo htmlspecialchars($room['room_name'], ENT_QUOTES); ?>" onclick="openRoomImageModal(this.dataset.roomId, this.dataset.roomName)" class="flex-1 px-3 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-50 transition">
                                <i class="bi bi-images"></i> Images
                            </button>
                            <button onclick="deleteRoom(<?php echo $room['resort_room_id']; ?>)" class="flex-1 px-3 py-2 border border-red-500 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php if($rooms->rowCount() == 0): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-door-closed text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No rooms added yet</h3>
            <p class="text-gray-500 mb-4">Add your first room or cottage.</p>
            <button onclick="openRoomModal()" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">Add Room</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Amenities Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
                <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="bi bi-plus-circle text-emerald-500"></i> Add Amenity</h2>
                <form method="POST">
                    <input type="hidden" name="add_amenity" value="1">
                    <div class="mb-3">
                        <label class="block text-sm text-gray-600 mb-1">Amenity Name</label>
                        <input type="text" name="amenity_name" class="w-full px-4 py-2 border border-gray-200 rounded-xl" placeholder="e.g. Pool Table, Karaoke" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Price (₱)</label>
                        <input type="number" name="amenity_price" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" step="0.01" value="0">
                    </div>
                    <button type="submit" class="w-full py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl text-sm font-medium hover:shadow-lg transition">Add Amenity</button>
                </form>
            </div>
        </div>
        
        <div class="lg:col-span-2">
            <h2 class="text-lg font-bold text-gray-800 mb-4">All Amenities</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">#</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Amenity</th>
                                <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Price</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            $amNum = 0;
                            while($am = $amenities->fetch()): 
                                $amNum++;
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 text-gray-600"><?php echo $amNum; ?></td>
                                <td class="px-5 py-3 flex items-center gap-3">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-star-fill text-emerald-600 text-xs"></i>
                                    </div>
                                    <span class="font-medium text-gray-800"><?php echo htmlspecialchars($am['amenity_name']); ?></span>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-800">₱<?php echo number_format($am['amenity_price'], 2); ?></td>
                                <td class="px-5 py-3 text-center">
                                    <a href="?page=resort-rooms&delete_amenity=<?php echo $am['amenity_id']; ?>" onclick="return confirm('Remove this amenity?')" class="text-red-500 hover:text-red-700 text-sm">
                                        <i class="bi bi-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($amNum === 0): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    <i class="bi bi-stars text-gray-300 text-2xl mb-2 block"></i>
                                    No amenities added yet
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Room Modal -->
<div id="roomModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Add Room</h3>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="room_id" id="roomId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Room Name *</label>
                <input type="text" name="room_name" id="roomName" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacity *</label>
                    <input type="number" name="capacity" id="capacity" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱) *</label>
                    <input type="number" name="price" id="price" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" min="0" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="room_description" id="roomDescription" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" placeholder="Describe this room..."></textarea>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select name="status" id="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl">
                    <option value="Available">Available</option>
                    <option value="Not Available">Not Available</option>
                </select>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeRoomModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="button" id="submitBtn" onclick="submitRoom()" class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">Add Room</button>
            </div>
        </form>
    </div>
</div>

<!-- Success Message Toast -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2">
        <i class="bi bi-check-circle"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<!-- Fee Edit Modal -->
<div id="feeModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Update Entrance Fee</h3>
        </div>
        <div class="p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2" id="feeLabel">Adult Fee</label>
            <input type="number" id="feeInput" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-lg" min="0">
            <div class="flex gap-3 mt-4">
                <button onclick="closeFeeModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
                <button onclick="saveFee()" class="flex-1 py-2 bg-purple-500 text-white rounded-xl font-medium hover:bg-purple-600 transition">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Room Image Modal -->
<div id="roomImageModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800" id="roomImageModalTitle">Room Images</h3>
            <button onclick="closeRoomImageModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="roomImageUploadForm" class="mb-6 p-4 border-2 border-dashed border-gray-200 rounded-xl">
                <div class="flex flex-col md:flex-row gap-3 items-start md:items-center">
                    <input type="file" name="files[]" multiple accept="image/*" class="flex-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                        <i class="bi bi-cloud-upload"></i> Upload
                    </button>
                </div>
            </form>
            <div id="roomImageGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
            <div id="roomImageEmpty" class="hidden text-center py-12 text-gray-500">
                <i class="bi bi-image text-4xl text-gray-300 block mb-2"></i>
                <p>No images for this room yet</p>
                <p class="text-sm text-gray-400">Upload images above to showcase this room</p>
            </div>
        </div>
    </div>
</div>

<script>
let feeType = 'adult';
let isEditRoom = false;

function openRoomModal() {
    isEditRoom = false;
    document.getElementById('modalTitle').textContent = 'Add Room';
    document.getElementById('roomId').value = '';
    document.getElementById('roomName').value = '';
    document.getElementById('roomDescription').value = '';
    document.getElementById('capacity').value = '';
    document.getElementById('price').value = '';
    document.getElementById('status').value = 'Available';
    document.getElementById('submitBtn').textContent = 'Add Room';
    document.getElementById('roomModal').classList.remove('hidden');
}

function editRoom(id, data) {
    isEditRoom = true;
    document.getElementById('modalTitle').textContent = 'Edit Room';
    document.getElementById('roomId').value = id;
    document.getElementById('roomName').value = data.roomName;
    document.getElementById('roomDescription').value = data.roomDescription;
    document.getElementById('capacity').value = data.roomCapacity;
    document.getElementById('price').value = data.roomPrice;
    document.getElementById('status').value = data.roomStatus;
    document.getElementById('submitBtn').textContent = 'Save Changes';
    document.getElementById('roomModal').classList.remove('hidden');
}

function closeRoomModal() {
    document.getElementById('roomModal').classList.add('hidden');
}

function showToast(message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function submitRoom() {
    const id = document.getElementById('roomId').value;
    const name = document.getElementById('roomName').value;
    const description = document.getElementById('roomDescription').value;
    const capacity = document.getElementById('capacity').value;
    const price = document.getElementById('price').value;
    const status = document.getElementById('status').value;

    if (!name || !capacity) {
        alert('Please fill in all required fields');
        return;
    }

    const formData = new FormData();
    formData.append('resortid', <?php echo $resortId; ?>);
    formData.append('room_name', name);
    formData.append('room_description', description);
    formData.append('room_capacity', capacity);
    formData.append('room_price', price);
    formData.append('room_status', status);

    let url = 'api/add-room.php';

    if (isEditRoom) {
        formData.append('room_id', id);
        url = 'api/update-room.php';
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message);
            closeRoomModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(err => alert('Error: ' + err));
}

function deleteRoom(id) {
    if (!confirm('Delete this room?')) return;

    const formData = new FormData();
    formData.append('room_id', id);
    formData.append('delete_room', '1');
    formData.append('ajax', '1');

    fetch('?page=resort-rooms', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message);
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(err => alert('Error: ' + err));
}

function editFee(type, currentVal) {
    feeType = type;
    document.getElementById('feeLabel').textContent = type === 'adult' ? 'Adult Entrance Fee' : 'Kids Entrance Fee';
    document.getElementById('feeInput').value = currentVal;
    document.getElementById('feeModal').classList.remove('hidden');
}

function closeFeeModal() {
    document.getElementById('feeModal').classList.add('hidden');
}

function saveFee() {
    const val = document.getElementById('feeInput').value;
    const url = feeType === 'adult' ? 'api/update-adult-fee.php' : 'api/update-kids-fee.php';
    const field = feeType === 'adult' ? 'adultEntranceFee' : 'kidsEntranceFee';

    const formData = new FormData();
    formData.append(field, val);
    formData.append('resortid', <?php echo $resortId; ?>);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if($.trim(response) === 'OK') {
                location.reload();
            } else {
                alert('Error updating fee: ' + response);
            }
        }
    });
}

let currentRoomImageId = 0;

function openRoomImageModal(roomId, roomName) {
    currentRoomImageId = roomId;
    document.getElementById('roomImageModalTitle').textContent = 'Images - ' + roomName;
    document.getElementById('roomImageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loadRoomImages(roomId);
}

function closeRoomImageModal() {
    document.getElementById('roomImageModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentRoomImageId = 0;
}

function loadRoomImages(roomId) {
    fetch('api/get-room-images.php?resort_room_id=' + roomId)
        .then(res => res.json())
        .then(images => {
            const grid = document.getElementById('roomImageGrid');
            const empty = document.getElementById('roomImageEmpty');
            grid.innerHTML = '';
            if (images.length === 0) {
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');
            images.forEach(function(img) {
                const col = document.createElement('div');
                col.className = 'relative group aspect-square rounded-xl overflow-hidden bg-gray-100';

                const imgEl = document.createElement('img');
                imgEl.src = 'uploads_flow/' + img.file_name;
                imgEl.className = 'w-full h-full object-cover';
                imgEl.onerror = function() {
                    this.parentElement.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gray-400"><i class="bi bi-image" style="font-size:2rem"></i></div>';
                };
                col.appendChild(imgEl);

                const delBtn = document.createElement('button');
                delBtn.className = 'absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hover:bg-red-600 shadow-lg';
                delBtn.innerHTML = '<i class="bi bi-trash text-sm"></i>';
                delBtn.onclick = function() { deleteRoomImage(img.id); };
                col.appendChild(delBtn);

                grid.appendChild(col);
            });
        });
}

function deleteRoomImage(imageId) {
    if (!confirm('Delete this image?')) return;
    const formData = new FormData();
    formData.append('imageid', imageId);
    fetch('api/delete-photo.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.text(); })
    .then(function(data) {
        if (data === 'OK') {
            showToast('Image deleted');
            loadRoomImages(currentRoomImageId);
        }
    });
}

document.getElementById('roomImageUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!currentRoomImageId) return;
    const formData = new FormData(this);
    formData.append('resort_room_id', currentRoomImageId);
    formData.append('submit', '1');
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Uploading...';
    fetch('api/upload-room-photo.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            showToast(data.message);
            loadRoomImages(currentRoomImageId);
            document.getElementById('roomImageUploadForm').reset();
        } else {
            alert(data.message);
        }
    })
    .catch(function(err) { alert('Error: ' + err); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-upload"></i> Upload';
    });
});

document.getElementById('roomImageModal').addEventListener('click', function(e) {
    if(e.target === this) closeRoomImageModal();
});

document.getElementById('roomModal').addEventListener('click', function(e) {
    if(e.target === this) closeRoomModal();
});
document.getElementById('feeModal').addEventListener('click', function(e) {
    if(e.target === this) closeFeeModal();
});
</script>

