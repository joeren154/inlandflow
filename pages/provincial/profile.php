<?php
$provincialId = $_SESSION['user_reg'];
$provincial = $conn->prepare("SELECT * FROM tb_provincial WHERE provid = ?");
$provincial->execute([$provincialId]);
$provincialData = $provincial->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $update = $conn->prepare("UPDATE tb_provincial SET username = ?, password = ? WHERE provid = ?");
    $update->execute([$username, $password, $provincialId]);
    $success = "Profile updated successfully!";
    
    $provincial = $conn->prepare("SELECT * FROM tb_provincial WHERE provid = ?");
    $provincial->execute([$provincialId]);
    $provincialData = $provincial->fetch();
}
?>

<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Settings</h1>
            <p class="text-gray-500">Manage your account credentials</p>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
            <span class="text-emerald-700"><?php echo $success; ?></span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-500 to-emerald-500 rounded-full flex items-center justify-center">
                    <i class="bi bi-shield-check text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Provincial Administrator</h2>
                    <p class="text-gray-500">Iloilo Province</p>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($provincialData['username']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="text" name="password" value="<?php echo htmlspecialchars($provincialData['password']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>