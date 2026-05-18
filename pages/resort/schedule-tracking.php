<?php
$resortId = $_SESSION['user_reg'];
$success = '';
$error = '';

// Process add schedule
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_schedule'])) {
    $staffId = $_POST['staff_id'] ?? '';
    $shiftDate = $_POST['shift_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $shiftType = $_POST['shift_type'] ?? 'Day';
    
    if(empty($staffId) || empty($shiftDate) || empty($startTime) || empty($endTime)) {
        $error = "Please fill in all required fields!";
    } else {
        try {
            $maxId = $conn->query("SELECT MAX(schedule_id) as max_id FROM tb_staff_schedule")->fetch();
            $newScheduleId = ($maxId['max_id'] ?? 0) + 1;
            
            $insert = $conn->prepare("INSERT INTO tb_staff_schedule (schedule_id, staff_id, resortid, shift_date, start_time, end_time, shift_type, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled')");
            $insert->execute([$newScheduleId, $staffId, $resortId, $shiftDate, $startTime, $endTime, $shiftType]);
            header('Location: index.php?page=resort-schedule&added=1');
        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Process delete schedule
if(isset($_GET['delete'])) {
    $scheduleId = $_GET['delete'];
    $delete = $conn->prepare("DELETE FROM tb_staff_schedule WHERE schedule_id = ? AND resortid = ?");
    $delete->execute([$scheduleId, $resortId]);
    header('Location: index.php?page=resort-schedule&deleted=1');
    exit;
}

// Success messages
if(isset($_GET['added']) && $_GET['added'] == 1) {
    $success = "Schedule added successfully!";
}
if(isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $success = "Schedule deleted successfully!";
}

// Get schedules grouped by date
$schedules = $conn->prepare("
    SELECT s.*, st.first_name, st.last_name, st.position
    FROM tb_staff_schedule s
    JOIN tb_staff st ON s.staff_id = st.staff_id
    WHERE s.resortid = ?
    ORDER BY s.shift_date ASC, s.start_time ASC
");
$schedules->execute([$resortId]);

// Group schedules by date
$scheduleByDate = [];
while($schedule = $schedules->fetch()) {
    $dateKey = date('Y-m-d', strtotime($schedule['shift_date']));
    if(!isset($scheduleByDate[$dateKey])) {
        $scheduleByDate[$dateKey] = [];
    }
    $scheduleByDate[$dateKey][] = $schedule;
}

// Get staff list
$staffList = $conn->prepare("SELECT staff_id, first_name, last_name, position FROM tb_staff WHERE resortid = ? AND status = 'Active'");
$staffList->execute([$resortId]);
$staffMembers = $staffList->fetchAll();
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 pt-20">
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                    Staff Schedule
                </h1>
                <p class="text-slate-600 mt-2">Manage staff shifts</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <button onclick="location.href='?page=resort-staff'" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Staff
                </button>
                <button onclick="showAddScheduleModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i> New Schedule
                </button>
            </div>
        </div>
        
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
        
        <!-- Schedule by Date -->
        <?php if(count($scheduleByDate) > 0): ?>
        <div class="space-y-6">
            <?php foreach($scheduleByDate as $date => $daySchedules): 
                $isToday = (strtotime($date) == strtotime(date('Y-m-d')));
                $isPast = strtotime($date) < strtotime(date('Y-m-d'));
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 <?php echo $isToday ? 'bg-purple-500 text-white' : ($isPast ? 'bg-gray-400 text-white' : 'bg-emerald-500 text-white'); ?>">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">
                            <i class="bi bi-calendar3 me-2"></i>
                            <?php echo date('l, F j, Y', strtotime($date)); ?>
                            <?php if($isToday): ?>
                            <span class="ml-2 px-2 py-0.5 bg-white/20 rounded text-xs">Today</span>
                            <?php endif; ?>
                        </h3>
                        <span class="text-sm opacity-80"><?php echo count($daySchedules); ?> shift(s)</span>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100">
                    <?php foreach($daySchedules as $shift): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-500 to-emerald-500 flex items-center justify-center text-white font-bold">
                                <?php echo substr($shift['first_name'], 0, 1); ?>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($shift['first_name'] . ' ' . $shift['last_name']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($shift['position']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($shift['shift_type']); ?></span>
                                <p class="text-sm text-gray-500"><?php echo date('g:i A', strtotime($shift['start_time'])); ?> - <?php echo date('g:i A', strtotime($shift['end_time'])); ?></p>
                            </div>
                            <button onclick="deleteSchedule(<?php echo $shift['schedule_id']; ?>)" class="px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl p-12 text-center">
            <i class="bi bi-calendar-week text-6xl text-slate-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-slate-700 mb-2">No schedules yet</h3>
            <p class="text-slate-500 mb-4">Create schedules to manage staff shifts.</p>
            <button onclick="showAddScheduleModal()" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium">
                <i class="bi bi-plus-circle me-2"></i> Add Schedule
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Schedule Modal -->
<div id="addScheduleModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-emerald-500 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="bi bi-calendar-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Add Schedule</h3>
                        <p class="text-purple-100 text-sm">Create staff shift</p>
                    </div>
                </div>
                <button onclick="closeAddScheduleModal()" class="text-white/70 hover:text-white">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Staff *</label>
                    <select name="staff_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                        <option value="">Choose staff member</option>
                        <?php foreach($staffMembers as $staff): ?>
                        <option value="<?php echo $staff['staff_id']; ?>"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name'] . ' - ' . $staff['position']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift Date *</label>
                    <input type="date" name="shift_date" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                        <input type="time" name="start_time" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                        <input type="time" name="end_time" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift Type</label>
                    <select name="shift_type" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl">
                        <option value="Day">Day Shift</option>
                        <option value="Night">Night Shift</option>
                        <option value="Morning">Morning Shift</option>
                        <option value="Afternoon">Afternoon Shift</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeAddScheduleModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl">Cancel</button>
                <button type="submit" name="add_schedule" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-emerald-500 text-white rounded-xl">
                    <i class="bi bi-check-lg me-2"></i>Add Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddScheduleModal() {
    document.getElementById('addScheduleModal').style.display = 'flex';
}

function closeAddScheduleModal() {
    document.getElementById('addScheduleModal').style.display = 'none';
}

function deleteSchedule(scheduleId) {
    if(confirm('Delete this schedule?')) {
        window.location.href = '?page=resort-schedule&delete=' + scheduleId;
    }
}

document.getElementById('addScheduleModal').addEventListener('click', function(e) {
    if(e.target === this) closeAddScheduleModal();
});
</script>