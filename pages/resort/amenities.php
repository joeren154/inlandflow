<?php
$resortId = $_SESSION['user_reg'];

// Handle delete via POST for proper JSON response
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_amenity'])) {
    $amenityId = $_POST['amenity_id'];
    
    $delete = $conn->prepare("DELETE FROM tb_resort_amenities WHERE amenity_id = ? AND resortid = ?");
    $delete->execute([$amenityId, $resortId]);
    
    if(isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Amenity deleted successfully!']);
        exit;
    }
}

$amenities = $conn->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ?");
$amenities->execute([$resortId]);
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Amenities Management</h1>
            <p class="text-gray-500">Manage amenities added for your resort</p>
        </div>
        <button onclick="openModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center gap-2">
            <i class="bi bi-plus-circle"></i> Add Amenity
        </button>
    </div>
    
    <?php if(isset($success)): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
        <span class="text-emerald-700"><?php echo $success; ?></span>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Amenity Name</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Price</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($amenity = $amenities->fetch()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($amenity['amenity_name']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-emerald-600 font-semibold">₱<?php echo number_format($amenity['amenity_price'], 0); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <button onclick="editAmenity(<?php echo $amenity['amenity_id']; ?>, '<?php echo htmlspecialchars($amenity['amenity_name']); ?>', <?php echo $amenity['amenity_price']; ?>)" class="text-purple-600 hover:text-purple-700 mr-3">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button onclick="deleteAmenity(<?php echo $amenity['amenity_id']; ?>)" class="text-red-600 hover:text-red-700">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($amenities->rowCount() == 0): ?>
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-gem text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No amenities added yet</h3>
            <p class="text-gray-500 mb-4">Add amenities that guests can purchase.</p>
            <button onclick="openModal()" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                Add Amenity
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="amenityModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Add Amenity</h3>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="amenity_id" id="amenityId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Amenity Name *</label>
                <input type="text" name="amenity_name" id="amenityName" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱) *</label>
                <input type="number" name="amenity_price" id="amenityPrice" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" min="0" required>
            </div>
            
        <div class="flex gap-3">
            <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" id="submitBtn" onclick="submitAmenity()" class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">
                Add Amenity
            </button>
        </div>
    </form>
</div>

<!-- Success Message Toast -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2">
        <i class="bi bi-check-circle"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<script>
let isEdit = false;

function openModal() {
    isEdit = false;
    document.getElementById('modalTitle').textContent = 'Add Amenity';
    document.getElementById('amenityId').value = '';
    document.getElementById('amenityName').value = '';
    document.getElementById('amenityPrice').value = '';
    document.getElementById('submitBtn').textContent = 'Add Amenity';
    document.getElementById('amenityModal').classList.remove('hidden');
}

function editAmenity(id, name, price) {
    isEdit = true;
    document.getElementById('modalTitle').textContent = 'Edit Amenity';
    document.getElementById('amenityId').value = id;
    document.getElementById('amenityName').value = name;
    document.getElementById('amenityPrice').value = price;
    document.getElementById('submitBtn').textContent = 'Save Changes';
    document.getElementById('amenityModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('amenityModal').classList.add('hidden');
}

function showToast(message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function submitAmenity() {
    const id = document.getElementById('amenityId').value;
    const name = document.getElementById('amenityName').value;
    const price = document.getElementById('amenityPrice').value;

    if (!name) {
        alert('Please enter amenity name');
        return;
    }

    const formData = new FormData();
    formData.append('resortid', <?php echo $resortId; ?>);
    formData.append('amenity_name', name);
    formData.append('amenity_price', price);

    let url = 'api/add-amenity.php';

    if (isEdit) {
        formData.append('amenity_id', id);
        url = 'api/update-amenity.php';
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message);
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(err => alert('Error: ' + err));
}

function deleteAmenity(id) {
    if (!confirm('Delete this amenity?')) return;

    const formData = new FormData();
    formData.append('amenity_id', id);
    formData.append('ajax', '1');

    fetch('?page=resort-amenities', {
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

document.getElementById('amenityModal').addEventListener('click', function(e) {
    if(e.target === this) {
        closeModal();
    }
});
</script>