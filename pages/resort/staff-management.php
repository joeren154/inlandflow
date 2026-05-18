<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$resortId = $_SESSION['user_reg'];
$success = '';
$error = '';

// Check if new staff was added or updated
if(isset($_GET['added']) && $_GET['added'] == 1) {
    $success = "Staff member added successfully!";
}
if(isset($_GET['updated']) && $_GET['updated'] == 1) {
    $success = "Staff member updated successfully!";
}

// Get all staff members
$staff = $conn->prepare("SELECT * FROM tb_staff WHERE resortid = ? ORDER BY hire_date DESC");
$staff->execute([$resortId]);

// Add new staff
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_staff'])) {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $position = $_POST['position'] ?? '';
    $hireDate = $_POST['hire_date'] ?? '';
    
    if(empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($position) || empty($hireDate)) {
        $error = "All fields are required!";
    } else {
        try {
            $maxId = $conn->query("SELECT MAX(staff_id) as max_id FROM tb_staff")->fetch();
            $newStaffId = ($maxId['max_id'] ?? 0) + 1;
            
$insert = $conn->prepare("INSERT INTO tb_staff (staff_id, resortid, first_name, last_name, email, phone, position, hire_date, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$newStaffId, $resortId, $firstName, $lastName, $email, $phone, $position, $hireDate, 'Active']);
            
            $_SESSION['staff_added'] = "Staff member added successfully!";
            header('Location: index.php?page=resort-staff&added=1');
        } catch(Exception $e) {
            $error = "Error adding staff: " . $e->getMessage();
        }
    }
}

// Edit staff
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_staff'])) {
    $staffId = $_POST['staff_id'];
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $position = $_POST['position'] ?? '';
    $hireDate = $_POST['hire_date'] ?? '';
    
    if(empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($position) || empty($hireDate)) {
        $error = "All fields are required!";
    } else {
        try {
            $update = $conn->prepare("UPDATE tb_staff SET first_name = ?, last_name = ?, email = ?, phone = ?, position = ?, hire_date = ? WHERE staff_id = ? AND resortid = ?");
            $update->execute([$firstName, $lastName, $email, $phone, $position, $hireDate, $staffId, $resortId]);
            
            header('Location: index.php?page=resort-staff&updated=1');
        } catch(Exception $e) {
            $error = "Error updating staff: " . $e->getMessage();
        }
    }
}

// Toggle staff status via AJAX
if(isset($_GET['toggle_status']) && isset($_GET['staff_id'])) {
    $staffId = $_GET['staff_id'];
    $staff = $conn->prepare("SELECT status FROM tb_staff WHERE staff_id = ? AND resortid = ?");
    $staff->execute([$staffId, $resortId]);
    $current = $staff->fetch();
    if($current) {
        $newStatus = $current['status'] == 'Active' ? 'Inactive' : 'Active';
        $update = $conn->prepare("UPDATE tb_staff SET status = ? WHERE staff_id = ?");
        $update->execute([$newStatus, $staffId]);
    }
    header('Location: index.php?page=resort-staff&toggled=1');
    exit;
}

// Delete staff
if(isset($_GET['delete_staff']) && isset($_GET['staff_id'])) {
    $staffId = $_GET['staff_id'];
    
    // Delete staff tasks first
    $deleteTasks = $conn->prepare("DELETE FROM tb_task_assignment WHERE staff_id = ? AND resortid = ?");
    $deleteTasks->execute([$staffId, $resortId]);
    
    // Delete staff schedules
    $deleteSchedules = $conn->prepare("DELETE FROM tb_staff_schedule WHERE staff_id = ? AND resortid = ?");
    $deleteSchedules->execute([$staffId, $resortId]);
    
    // Delete staff
    $deleteStaff = $conn->prepare("DELETE FROM tb_staff WHERE staff_id = ? AND resortid = ?");
    $deleteStaff->execute([$staffId, $resortId]);
    
    header('Location: index.php?page=resort-staff&deleted=1');
    exit;
}

// Toggle/ delete success messages
if(isset($_GET['toggled']) && $_GET['toggled'] == 1) {
    $success = "Staff status updated!";
}
if(isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $success = "Staff member deleted!";
}

// Get all staff for display
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 pt-20">
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8" data-aos="fade-up">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                    Staff Management
                </h1>
                <p class="text-slate-600 mt-2">Manage your resort staff and their assignments</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <button onclick="showAddStaffModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center gap-2">
                    <i class="bi bi-person-plus"></i> Add Staff
                </button>
                <button onclick="location.href='?page=resort-tasks'" class="px-4 py-2 bg-purple-100 text-purple-600 rounded-xl flex items-center gap-2">
                    <i class="bi bi-list-check"></i> Tasks
                </button>
                <button onclick="location.href='?page=resort-schedule'" class="px-4 py-2 bg-emerald-100 text-emerald-600 rounded-xl flex items-center gap-2">
                    <i class="bi bi-calendar-week"></i> Schedule
                </button>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Staff Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($member = $staff->fetch()): ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up">
                <div class="bg-gradient-to-r from-purple-500 to-emerald-500 p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h3>
                            <p class="text-white/80 text-sm"><?php echo htmlspecialchars($member['position']); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="bi bi-person-badge text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="bi bi-envelope w-5"></i>
                            <span class="text-sm"><?php echo htmlspecialchars($member['email']); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="bi bi-telephone w-5"></i>
                            <span class="text-sm"><?php echo htmlspecialchars($member['phone']); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="bi bi-calendar w-5"></i>
                            <span class="text-sm">Hired: <?php echo date('M j, Y', strtotime($member['hire_date'])); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold">Status:</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?php echo $member['status'] == 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo $member['status']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between gap-2">
                        <button onclick="openEditModal(<?php echo $member['staff_id']; ?>, '<?php echo htmlspecialchars($member['first_name']); ?>', '<?php echo htmlspecialchars($member['last_name']); ?>', '<?php echo htmlspecialchars($member['email']); ?>', '<?php echo htmlspecialchars($member['phone']); ?>', '<?php echo htmlspecialchars($member['position']); ?>', '<?php echo $member['hire_date']; ?>')" class="flex-1 px-3 py-1.5 border border-purple-500 text-purple-600 rounded-lg text-sm hover:bg-purple-50">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        <button onclick="toggleStatus(<?php echo $member['staff_id']; ?>, '<?php echo $member['status']; ?>')" class="flex-1 px-3 py-1.5 border border-amber-500 text-amber-600 rounded-lg text-sm hover:bg-amber-50">
                            <i class="bi bi-power me-1"></i> <?php echo $member['status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                        </button>
                        <button onclick="viewStaffTasks(<?php echo $member['staff_id']; ?>)" class="flex-1 px-3 py-1.5 bg-purple-100 text-purple-600 rounded-lg text-sm">
                            <i class="bi bi-tasks me-1"></i> Tasks
                        </button>
                        <button onclick="deleteStaff(<?php echo $member['staff_id']; ?>, '<?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>')" class="flex-1 px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200">
                            <i class="bi bi-trash me-1"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php if($staff->rowCount() == 0): ?>
        <div class="bg-white rounded-2xl p-12 text-center">
            <i class="bi bi-people text-6xl text-slate-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-slate-700 mb-2">No staff members yet</h3>
            <p class="text-slate-500 mb-4">Add your first staff member to get started.</p>
            <button onclick="showAddStaffModal()" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">
                            <i class="bi bi-person-plus me-2"></i> Add Staff Member
                        </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Staff Modal -->
<div id="addStaffModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-emerald-500 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="bi bi-person-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Add New Staff</h3>
                        <p class="text-purple-100 text-sm">Enter staff member details</p>
                    </div>
                </div>
                <button onclick="closeAddStaffModal()" class="text-white/70 hover:text-white">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" class="p-6">
            <div class="space-y-4">
                <!-- Name Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" placeholder="John" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" placeholder="Doe" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" placeholder="john.doe@resort.com" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>
                
                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <div class="relative">
                        <i class="bi bi-telephone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" name="phone" placeholder="0912 345 6789" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>
                
                <!-- Position & Date Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                        <select name="position" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                            <option value="">Select Position</option>
                            <option value="Manager">Manager</option>
                            <option value="Front Desk">Front Desk</option>
                            <option value="Housekeeping">Housekeeping</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Security">Security</option>
                            <option value="Food Service">Food Service</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                        <input type="date" name="hire_date" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeAddStaffModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" name="add_staff" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-emerald-500 text-white rounded-xl font-medium hover:shadow-lg transition">
                    <i class="bi bi-check-lg me-2"></i>Add Staff
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-emerald-500 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="bi bi-person-badge text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Edit Staff</h3>
                        <p class="text-purple-100 text-sm">Update staff details</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="text-white/70 hover:text-white">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" id="editStaffForm" class="p-6">
            <input type="hidden" name="edit_staff" value="1">
            <input type="hidden" name="staff_id" id="editStaffId">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" id="editFirstName" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" id="editLastName" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" id="editEmail" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                    <input type="tel" name="phone" id="editPhone" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                        <select name="position" id="editPosition" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl">
                            <option value="Manager">Manager</option>
                            <option value="Front Desk">Front Desk</option>
                            <option value="Housekeeping">Housekeeping</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Security">Security</option>
                            <option value="Food Service">Food Service</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                        <input type="date" name="hire_date" id="editHireDate" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-emerald-500 text-white rounded-xl">
                    <i class="bi bi-check-lg me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddStaffModal() {
    document.getElementById('addStaffModal').style.display = 'flex';
}

function closeAddStaffModal() {
    document.getElementById('addStaffModal').style.display = 'none';
}

function editStaff(staffId) {
    window.location.href = '?page=resort-staff&edit=' + staffId;
}

function viewStaffTasks(staffId) {
    window.location.href = '?page=resort-tasks&staff_id=' + staffId;
}

document.getElementById('addStaffModal').addEventListener('click', function(e) {
    if(e.target === this) closeAddStaffModal();
});

function openEditModal(staffId, firstName, lastName, email, phone, position, hireDate) {
    document.getElementById('editStaffId').value = staffId;
    document.getElementById('editFirstName').value = firstName;
    document.getElementById('editLastName').value = lastName;
    document.getElementById('editEmail').value = email;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editPosition').value = position;
    document.getElementById('editHireDate').value = hireDate;
    document.getElementById('editStaffModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editStaffModal').style.display = 'none';
}

function toggleStatus(staffId, currentStatus) {
    var newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    if(confirm('Change status to ' + newStatus + '?')) {
        window.location.href = '?page=resort-staff&toggle_status=1&staff_id=' + staffId;
    }
}

function deleteStaff(staffId, staffName) {
    if(confirm('Are you sure you want to delete ' + staffName + '? This will also remove all their tasks and schedules.')) {
        window.location.href = '?page=resort-staff&delete_staff=1&staff_id=' + staffId;
    }
}

document.getElementById('editStaffModal').addEventListener('click', function(e) {
    if(e.target === this) closeEditModal();
});
</script>
