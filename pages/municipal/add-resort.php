<?php
$municipalId = $_SESSION['user_reg'];
$municipal = $conn->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$municipal->execute([$municipalId]);
$municipalData = $municipal->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_resort'])) {
    $resortname = $_POST['resortname'];
    $resortaddress = $_POST['resortaddress'];
    $contact_no = $_POST['contact_no'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $district = $municipalData['district'];
    $mun = $municipalData['mun'];
    
    $check = $conn->prepare("SELECT resortid FROM tb_resort WHERE username = ?");
    $check->execute([$username]);
    
    if($check->rowCount() > 0) {
        $error = "Username already exists!";
    } else {
        $nextResortId = $conn->query("SELECT COALESCE(MAX(resortid), 0) + 1 AS next_id FROM tb_resort")->fetch()['next_id'];
        $insert = $conn->prepare("INSERT INTO tb_resort (resortid, resortname, resortaddress, mun, district, contact_no, username, password, status, isLocated) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Offline now', 0)");
        $insert->execute([$nextResortId, $resortname, $resortaddress, $mun, $district, $contact_no, $username, $password]);
        $success = "Resort added successfully!";
    }
}
?>

<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Add New Resort</h1>
            <p class="text-gray-500">Register a new resort in <?php echo htmlspecialchars($municipalData['mun']); ?> Municipality</p>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
            <div>
                <p class="font-semibold text-emerald-700"><?php echo $success; ?></p>
                <p class="text-sm text-emerald-600">Login credentials: Username: <?php echo htmlspecialchars($username); ?>, Password: <?php echo htmlspecialchars($password); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="bi bi-exclamation-circle text-red-600 text-xl"></i>
            <span class="text-red-700"><?php echo $error; ?></span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <form method="POST">
                <input type="hidden" name="add_resort" value="1">
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resort Name *</label>
                    <input type="text" name="resortname" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter resort name" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resort Address *</label>
                    <textarea name="resortaddress" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="Enter complete address" required></textarea>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                    <input type="tel" name="contact_no" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., 09123456789" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Login Username *</label>
                    <input type="text" name="username" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Username for resort login" required>
                    <p class="text-xs text-gray-500 mt-1">This will be used by the resort to login</p>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Login Password *</label>
                    <input type="text" name="password" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Default password" required>
                    <p class="text-xs text-gray-500 mt-1">Default password for the resort account</p>
                </div>
                
                <div class="bg-blue-50 rounded-xl p-4 mb-6">
                    <p class="text-sm text-blue-700">
                        <i class="bi bi-info-circle me-1"></i>
                        <span class="font-medium">Municipality:</span> <?php echo htmlspecialchars($municipalData['mun']); ?> <br>
                        <span class="font-medium">District:</span> <?php echo htmlspecialchars($municipalData['district']); ?>
                    </p>
                </div>
                
                <button type="submit" name="add_resort" class="w-full py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-building-add"></i> Register Resort
                </button>
            </form>
        </div>
    </div>
</div>