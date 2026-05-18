<?php
$resortId = $_SESSION['user_reg'];
$success = '';
$error = '';

// Process add task
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $staffId = $_POST['staff_id'] ?? '';
    $taskName = $_POST['task_name'] ?? '';
    $taskDescription = $_POST['task_description'] ?? '';
    $dueDate = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'Normal';
    
    if(empty($staffId) || empty($taskName) || empty($dueDate)) {
$error = "Please fill in all required fields!";
    } else {
        try {
            $maxId = $conn->query("SELECT MAX(task_id) as max_id FROM tb_task_assignment")->fetch();
            $newTaskId = ($maxId['max_id'] ?? 0) + 1;
            
            $insert = $conn->prepare("INSERT INTO tb_task_assignment (task_id, staff_id, resortid, task_name, task_description, due_date, priority, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $insert->execute([$newTaskId, $staffId, $resortId, $taskName, $taskDescription, $dueDate, $priority]);
            header('Location: index.php?page=resort-tasks&added=1');
        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Process update task status
if(isset($_GET['complete'])) {
    $taskId = $_GET['complete'];
    $update = $conn->prepare("UPDATE tb_task_assignment SET status = 'Completed', completed_at = NOW() WHERE task_id = ? AND resortid = ?");
    $update->execute([$taskId, $resortId]);
    header('Location: index.php?page=resort-tasks&completed=1');
    exit;
}

if(isset($_GET['pending'])) {
    $taskId = $_GET['pending'];
    $update = $conn->prepare("UPDATE tb_task_assignment SET status = 'Pending', completed_at = NULL WHERE task_id = ? AND resortid = ?");
    $update->execute([$taskId, $resortId]);
    header('Location: index.php?page=resort-tasks');
    exit;
}

if(isset($_GET['delete'])) {
    $taskId = $_GET['delete'];
    $delete = $conn->prepare("DELETE FROM tb_task_assignment WHERE task_id = ? AND resortid = ?");
    $delete->execute([$taskId, $resortId]);
    header('Location: index.php?page=resort-tasks&deleted=1');
    exit;
}

// Success messages
if(isset($_GET['added']) && $_GET['added'] == 1) {
    $success = "Task assigned successfully!";
}
if(isset($_GET['completed']) && $_GET['completed'] == 1) {
    $success = "Task marked as completed!";
}

// Get all tasks with staff info
$tasks = $conn->prepare("
    SELECT t.*, s.first_name, s.last_name, s.position
    FROM tb_task_assignment t
    JOIN tb_staff s ON t.staff_id = s.staff_id
    WHERE t.resortid = ?
    ORDER BY t.due_date ASC
");
$tasks->execute([$resortId]);

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
                    Task Assignments
                </h1>
                <p class="text-slate-600 mt-2">Assign tasks to your staff</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <button onclick="location.href='?page=resort-staff'" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Staff
                </button>
                <button onclick="showAddTaskModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i> New Task
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
        
        <!-- Tasks List -->
        <div class="space-y-4">
            <?php while($task = $tasks->fetch()): 
                $isOverdue = strtotime($task['due_date']) < time() && $task['status'] == 'Pending';
                $isCompleted = $task['status'] == 'Completed';
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden <?php echo $isCompleted ? 'opacity-75' : ''; ?>">
                <div class="flex items-center gap-4 p-5">
                    <!-- Priority Indicator -->
                    <div class="w-2 h-16 rounded-full <?php 
                        if($task['priority'] == 'High') echo 'bg-red-500';
                        elseif($task['priority'] == 'Normal') echo 'bg-yellow-500';
                        else echo 'bg-green-500';
                    ?>"></div>
                    
                    <!-- Task Details -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($task['task_name']); ?></h3>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php 
                                if($task['status'] == 'Pending') echo 'bg-yellow-100 text-yellow-700';
                                elseif($task['status'] == 'Completed') echo 'bg-green-100 text-green-700';
                            ?>">
                                <?php echo $task['status']; ?>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php 
                                if($task['priority'] == 'High') echo 'bg-red-100 text-red-700';
                                elseif($task['priority'] == 'Normal') echo 'bg-yellow-100 text-yellow-700';
                                else echo 'bg-green-100 text-green-700';
                            ?>">
                                <?php echo $task['priority']; ?> Priority
                            </span>
                        </div>
                        <?php if($task['task_description']): ?>
                        <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($task['task_description']); ?></p>
                        <?php endif; ?>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?>
                            </span>
                            <span class="flex items-center gap-1 <?php echo $isOverdue ? 'text-red-600' : ''; ?>">
                                <i class="bi bi-calendar"></i> Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-2">
                        <?php if($task['status'] == 'Pending'): ?>
                        <button onclick="completeTask(<?php echo $task['task_id']; ?>)" class="px-3 py-2 bg-green-100 text-green-600 rounded-lg text-sm hover:bg-green-200">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <?php else: ?>
                        <button onclick="pendingTask(<?php echo $task['task_id']; ?>)" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        <?php endif; ?>
                        <button onclick="deleteTask(<?php echo $task['task_id']; ?>)" class="px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if($tasks->rowCount() == 0): ?>
            <div class="bg-white rounded-2xl p-12 text-center">
                <i class="bi bi-list-task text-6xl text-slate-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-slate-700 mb-2">No tasks yet</h3>
                <p class="text-slate-500 mb-4">Create your first task to get started.</p>
                <button onclick="showAddTaskModal()" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium">
                    <i class="bi bi-plus-circle me-2"></i> Assign Task
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div id="addTaskModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-emerald-500 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="bi bi-list-task text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Assign New Task</h3>
                        <p class="text-purple-100 text-sm">Fill in task details</p>
                    </div>
                </div>
                <button onclick="closeAddTaskModal()" class="text-white/70 hover:text-white">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Name *</label>
                    <input type="text" name="task_name" placeholder="Clean room 301" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="task_description" rows="3" placeholder="Task details..." class="w-full px-4 py-2.5 border border-gray-200 rounded-xl"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
                        <input type="date" name="due_date" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl">
                            <option value="Low">Low</option>
                            <option value="Normal" selected>Normal</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeAddTaskModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl">Cancel</button>
                <button type="submit" name="add_task" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-emerald-500 text-white rounded-xl">
                    <i class="bi bi-check-lg me-2"></i>Assign Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddTaskModal() {
    document.getElementById('addTaskModal').style.display = 'flex';
}

function closeAddTaskModal() {
    document.getElementById('addTaskModal').style.display = 'none';
}

function completeTask(taskId) {
    if(confirm('Mark this task as completed?')) {
        window.location.href = '?page=resort-tasks&complete=' + taskId;
    }
}

function pendingTask(taskId) {
    window.location.href = '?page=resort-tasks&pending=' + taskId;
}

function deleteTask(taskId) {
    if(confirm('Delete this task?')) {
        window.location.href = '?page=resort-tasks&delete=' + taskId;
    }
}

document.getElementById('addTaskModal').addEventListener('click', function(e) {
    if(e.target === this) closeAddTaskModal();
});
</script>