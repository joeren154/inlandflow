<?php
$resortId = $_SESSION['user_reg'];

// View guest details
$viewGuestId = $_GET['view'] ?? 0;
$guestDetails = null;
$guestBookings = [];
if($viewGuestId) {
    $guestQ = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
    $guestQ->execute([$viewGuestId]);
    $guestDetails = $guestQ->fetch();
    
    if($guestDetails) {
        $bookingsQ = $conn->prepare("
            SELECT c.*, po.po_id, po.total_fee, po.reservation_status, po.ratings,
                   r.resortname, rr.room_name
            FROM tb_cart c
            JOIN tb_placed_order po ON c.cart_id = po.cart_id
            JOIN tb_resort r ON c.resortid = r.resortid
            LEFT JOIN tb_resort_room rr ON c.resort_room_id = rr.resort_room_id
            WHERE c.guest_id = ? AND c.resortid = ?
            ORDER BY c.checkindate DESC
        ");
        $bookingsQ->execute([$viewGuestId, $resortId]);
        $guestBookings = $bookingsQ->fetchAll();
    }
}

// Get all guests who booked this resort
$guests = $conn->prepare("
    SELECT DISTINCT g.*, 
           COUNT(c.cart_id) as total_visits,
           SUM(po.total_fee) as total_spent,
           MAX(c.checkindate) as last_visit
    FROM tb_guest g
    JOIN tb_cart c ON g.guest_id = c.guest_id
    JOIN tb_placed_order po ON c.cart_id = po.cart_id
    WHERE c.resortid = ? AND po.reservation_status = 'Completed'
    GROUP BY g.guest_id
    ORDER BY last_visit DESC
");
$guests->execute([$resortId]);

// Add new guest record
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_guest'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $middleName = $_POST['middle_name'];
    $contactNo = $_POST['contact_no'];
    $address = $_POST['address'];
    $visitDate = $_POST['visit_date'];
    $numAdults = $_POST['num_adults'];
    $numKids = $_POST['num_kids'];
    $notes = $_POST['notes'];
    
    // Check if guest exists
    $check = $conn->prepare("SELECT guest_id FROM tb_guest WHERE ContactNo = ?");
    $check->execute([$contactNo]);
    
    if($check->rowCount() > 0) {
        $guestId = $check->fetch()['guest_id'];
    } else {
        // Create new guest
        $username = strtolower($firstName . $lastName);
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $insert = $conn->prepare("INSERT INTO tb_guest (LastName, FirstName, MiddleName, ContactNo, Address, Username, Password, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
        $insert->execute([$lastName, $firstName, $middleName, $contactNo, $address, $username, $password]);
        $guestId = $conn->lastInsertId();
    }
    
    // Add to guest records
    $record = $conn->prepare("INSERT INTO tb_guest_records (guest_id, resortid, visit_date, num_visits, notes) 
                              VALUES (?, ?, ?, 1, ?)");
    $record->execute([$guestId, $resortId, $visitDate, $notes]);
    
    $success = "Guest record added successfully!";
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
if($search) {
    $guests = $conn->prepare("
        SELECT DISTINCT g.*, 
               COUNT(c.cart_id) as total_visits,
               SUM(po.total_fee) as total_spent,
               MAX(c.checkindate) as last_visit
        FROM tb_guest g
        JOIN tb_cart c ON g.guest_id = c.guest_id
        JOIN tb_placed_order po ON c.cart_id = po.cart_id
        WHERE c.resortid = ? AND po.reservation_status = 'Completed'
        AND (g.FirstName LIKE ? OR g.LastName LIKE ? OR g.ContactNo LIKE ?)
        GROUP BY g.guest_id
        ORDER BY last_visit DESC
    ");
    $guests->execute([$resortId, "%$search%", "%$search%", "%$search%"]);
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 pt-20">
    <div class="container mx-auto px-6 py-8">
        <?php if($guestDetails): ?>
        <!-- Guest Details View -->
        <div class="mb-6" data-aos="fade-up">
            <button onclick="window.location.href='?page=resort-guest-management'" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 mb-4">
                <i class="bi bi-arrow-left"></i> Back to Guest List
            </button>
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                            <?php echo htmlspecialchars($guestDetails['FirstName'] . ' ' . $guestDetails['LastName']); ?>
                        </h1>
                        <p class="text-slate-600 mt-1"><?php echo htmlspecialchars($guestDetails['ContactNo']); ?></p>
                        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($guestDetails['Address']); ?></p>
                    </div>
                </div>
                
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Booking History with Amenities</h2>
                <?php if(count($guestBookings) > 0): ?>
                <div class="space-y-4">
                    <?php foreach($guestBookings as $booking): 
                        $amenities = $conn->prepare("SELECT aoa.*, ra.amenity_name FROM tb_add_on_amenities aoa JOIN tb_resort_amenities ra ON aoa.amenity_id = ra.amenity_id WHERE aoa.po_id = ?");
                        $amenities->execute([$booking['po_id']]);
                        $amenitiesList = $amenities->fetchAll();
                    ?>
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-2">
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($booking['resortname']); ?></p>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($booking['checkindate'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($booking['checkoutdate'])); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-purple-600">₱<?php echo number_format($booking['total_fee'], 2); ?></p>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                    <?php 
                                    $status = $booking['reservation_status'];
                                    echo $status === 'Completed' ? 'bg-emerald-100 text-emerald-700' : 
                                        ($status === 'Approved' ? 'bg-blue-100 text-blue-700' : 
                                        ($status === 'Reviewed' ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-700')); 
                                    ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        </div>
                        <?php if($booking['room_name']): ?>
                        <p class="text-sm text-purple-600 mb-2"><i class="bi bi-door-open"></i> <?php echo htmlspecialchars($booking['room_name']); ?></p>
                        <?php endif; ?>
                        <?php if(count($amenitiesList) > 0): ?>
                        <div class="mt-2 pt-2 border-t border-slate-100">
                            <p class="text-xs text-emerald-600 font-medium mb-1">Amenities Added:</p>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach($amenitiesList as $am): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 rounded text-xs text-emerald-700">
                                    <?php echo htmlspecialchars($am['amenity_name']); ?> x<?php echo $am['quantity']; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-slate-500 text-center py-4">No bookings found for this guest.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8" data-aos="fade-up">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                    Guest Management
                </h1>
                <p class="text-slate-600 mt-2">Track and manage guest information</p>
            </div>
            <div class="mt-4 md:mt-0">
                <button onclick="showAddGuestModal()" class="btn-gradient px-6 py-2 rounded-xl">
                    <i class="bi bi-person-plus me-2"></i> Add Guest Record
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Search Bar -->
        <div class="bg-white rounded-2xl p-4 shadow-lg mb-6" data-aos="fade-up">
            <div class="flex gap-4">
                <div class="flex-1 relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchInput" placeholder="Search by name or contact number..." 
                           class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-purple-400">
                </div>
                <button onclick="searchGuests()" class="px-4 py-2 bg-purple-100 text-purple-600 rounded-xl hover:bg-purple-200">
                    Search
                </button>
            </div>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <!-- Guests Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Guest Info</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Total Visits</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Total Spent</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Last Visit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if($guests->rowCount() > 0): ?>
                            <?php while($guest = $guests->fetch()): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-slate-800"><?php echo htmlspecialchars($guest['FirstName'] . ' ' . $guest['LastName']); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($guest['Address']); ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-600"><?php echo $guest['ContactNo']; ?></p>
                                 </td>
                                <td class="px-6 py-4 text-slate-600"><?php echo $guest['total_visits']; ?> times</td>
                                <td class="px-6 py-4 text-emerald-600 font-semibold">₱<?php echo number_format($guest['total_spent'], 2); ?></td>
                                <td class="px-6 py-4 text-slate-600"><?php echo date('M j, Y', strtotime($guest['last_visit'])); ?></td>
                                <td class="px-6 py-4">
                                    <button onclick="viewGuestDetails(<?php echo $guest['guest_id']; ?>)" 
                                            class="text-purple-600 hover:text-purple-700">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No guest records found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title text-xl font-bold">Add Guest Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">First Name *</label>
                            <input type="text" name="first_name" class="form-control rounded-xl" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control rounded-xl">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Last Name *</label>
                            <input type="text" name="last_name" class="form-control rounded-xl" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Number *</label>
                            <input type="tel" name="contact_no" class="form-control rounded-xl" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Visit Date *</label>
                            <input type="date" name="visit_date" class="form-control rounded-xl" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Number of Adults</label>
                            <input type="number" name="num_adults" class="form-control rounded-xl" value="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Number of Kids</label>
                            <input type="number" name="num_kids" class="form-control rounded-xl" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control rounded-xl" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control rounded-xl" rows="2" placeholder="Any special notes about this guest..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_guest" class="btn btn-primary bg-gradient-to-r from-purple-500 to-emerald-500">Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddGuestModal() {
    $('#addGuestModal').modal('show');
}

function searchGuests() {
    const search = document.getElementById('searchInput').value;
    window.location.href = '?page=resort-guest-management&search=' + encodeURIComponent(search);
}

function viewGuestDetails(guestId) {
    window.location.href = '?page=resort-guest-management&view=' + guestId;
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') searchGuests();
});
</script>