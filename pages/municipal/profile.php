<?php
$municipalId = $_SESSION['user_reg'];
$municipal = $conn->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$municipal->execute([$municipalId]);
$municipalData = $municipal->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $update = $conn->prepare("UPDATE tb_municipality SET username = ?, password = ? WHERE id = ?");
    $update->execute([$username, $password, $municipalId]);
    $success = "Profile updated successfully!";
    
    $municipal = $conn->prepare("SELECT * FROM tb_municipality WHERE id = ?");
    $municipal->execute([$municipalId]);
    $municipalData = $municipal->fetch();
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
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center">
                    <i class="bi bi-building text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($municipalData['mun']); ?></h2>
                    <p class="text-gray-500"><?php echo htmlspecialchars($municipalData['district']); ?></p>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($municipalData['username']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="text" name="password" value="<?php echo htmlspecialchars($municipalData['password']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>