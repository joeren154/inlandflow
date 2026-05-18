<?php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_municipality'])) {
    $mun = strtoupper($_POST['mun']);
    $district = $_POST['district'];
    $username = strtoupper($_POST['username']);
    $password = $_POST['password'];
    
    $check = $conn->prepare("SELECT id FROM tb_municipality WHERE mun = ?");
    $check->execute([$mun]);
    
    if($check->rowCount() > 0) {
        $error = "Municipality already exists!";
    } else {
        $nextId = $conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM tb_municipality")->fetch()['next_id'];
        $insert = $conn->prepare("INSERT INTO tb_municipality (id, mun, district, username, password) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$nextId, $mun, $district, $username, $password]);
        $success = "Municipality added successfully!";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_municipality'])) {
    $id = $_POST['id'];
    $mun = strtoupper($_POST['mun']);
    $district = $_POST['district'];
    $username = strtoupper($_POST['username']);
    $password = $_POST['password'];
    
    $update = $conn->prepare("UPDATE tb_municipality SET mun = ?, district = ?, username = ?, password = ? WHERE id = ?");
    $update->execute([$mun, $district, $username, $password, $id]);
    $success = "Municipality updated successfully!";
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_municipality'])) {
    $id = $_POST['id'];
    $delete = $conn->prepare("DELETE FROM tb_municipality WHERE id = ?");
    $delete->execute([$id]);
    $deleted = "Municipality deleted successfully!";
}

$municipalities = $conn->query("SELECT * FROM tb_municipality ORDER BY mun");
$districts = ['FIRST DISTRICT', 'SECOND DISTRICT', 'THIRD DISTRICT', 'FOURTH DISTRICT', 'FIFTH DISTRICT'];
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Municipality Management</h1>
            <p class="text-gray-500">Add and manage municipal accounts</p>
        </div>
    </div>

    <?php if(isset($success)): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
        <span class="text-emerald-700"><?php echo $success; ?></span>
    </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-exclamation-circle text-red-600 text-xl"></i>
        <span class="text-red-700"><?php echo $error; ?></span>
    </div>
    <?php endif; ?>

    <?php if(isset($deleted)): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-trash text-amber-600 text-xl"></i>
        <span class="text-amber-700"><?php echo $deleted; ?></span>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Municipality</h2>
            <form method="POST">
                <input type="hidden" name="add_municipality" value="1">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Municipality Name</label>
                    <input type="text" name="mun" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="e.g., OTON" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">District</label>
                    <select name="district" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                        <option value="">Select District</option>
                        <?php foreach($districts as $dist): ?>
                        <option value="<?php echo $dist; ?>"><?php echo $dist; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Username" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="text" name="password" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Password" required>
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
                    <i class="bi bi-plus-lg me-2"></i> Add Municipality
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Registered Municipalities</h2>
                <p class="text-gray-500 text-sm">All municipalities in the system</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Municipality</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase px-2 py-3">1st District</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase px-2 py-3">2nd District</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase px-2 py-3">3rd District</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase px-2 py-3">4th District</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase px-2 py-3">5th District</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-3 py-3">Username</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-3 py-3">Password</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php 
                        $districtMap = ['FIRST DISTRICT' => 1, 'SECOND DISTRICT' => 2, 'THIRD DISTRICT' => 3, 'FOURTH DISTRICT' => 4, 'FIFTH DISTRICT' => 5];
                        while($mun = $municipalities->fetch()): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($mun['mun']); ?></p>
                            </td>
                            <?php for($d = 1; $d <= 5; $d++): ?>
                            <td class="px-2 py-4 text-center">
                                <?php if(($districtMap[$mun['district']] ?? 0) == $d): ?>
                                <i class="bi bi-check-lg text-teal-500 text-lg"></i>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                            <td class="px-3 py-4">
                                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($mun['username']); ?></span>
                            </td>
                            <td class="px-3 py-4">
                                <span class="text-sm text-gray-500">••••••••</span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                <button onclick="openEditModal(<?php echo $mun['id']; ?>, '<?php echo htmlspecialchars($mun['mun']); ?>', '<?php echo htmlspecialchars($mun['district']); ?>', '<?php echo htmlspecialchars($mun['username']); ?>', '<?php echo htmlspecialchars($mun['password']); ?>')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-sm hover:bg-blue-100 transition">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="delete_municipality" value="1">
                                    <input type="hidden" name="id" value="<?php echo $mun['id']; ?>">
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition" onclick="return confirm('Delete this municipality?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Edit Municipality</h3>
            <p class="text-gray-500 text-sm">Update municipality credentials</p>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="edit_municipality" value="1">
            <input type="hidden" name="id" id="editId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Municipality Name</label>
                <input type="text" name="mun" id="editMun" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">District</label>
                <select name="district" id="editDistrict" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                    <?php foreach($districts as $dist): ?>
                    <option value="<?php echo $dist; ?>"><?php echo $dist; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" name="username" id="editUsername" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="text" name="password" id="editPassword" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl font-medium hover:shadow-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, mun, district, username, password) {
    document.getElementById('editId').value = id;
    document.getElementById('editMun').value = mun;
    document.getElementById('editDistrict').value = district;
    document.getElementById('editUsername').value = username;
    document.getElementById('editPassword').value = password;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if(e.target === this) {
        closeEditModal();
    }
});
</script>