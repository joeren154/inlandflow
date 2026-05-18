<?php
$resortId = $_SESSION['user_reg'];
$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $resortname = $_POST['resortname'];
    $resortaddress = $_POST['resortaddress'];
    $contact_no = $_POST['contact_no'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $adultFee = $_POST['adultEntranceFee'];
    $kidsFee = $_POST['kidsEntranceFee'];
    
    $update = $conn->prepare("UPDATE tb_resort SET resortname = ?, resortaddress = ?, contact_no = ?, username = ?, password = ?, adultEntranceFee = ?, kidsEntranceFee = ? WHERE resortid = ?");
    $update->execute([$resortname, $resortaddress, $contact_no, $username, $password, $adultFee, $kidsFee, $resortId]);
    $success = "Profile updated successfully!";
    
    $resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
    $resort->execute([$resortId]);
    $resortData = $resort->fetch();
}
?>

<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Settings</h1>
            <p class="text-gray-500">Manage your resort profile and credentials</p>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
            <span class="text-emerald-700"><?php echo $success; ?></span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resort Name *</label>
                    <input type="text" name="resortname" value="<?php echo htmlspecialchars($resortData['resortname']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resort Address *</label>
                    <textarea name="resortaddress" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="2" required><?php echo htmlspecialchars($resortData['resortaddress']); ?></textarea>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                    <input type="tel" name="contact_no" value="<?php echo htmlspecialchars($resortData['contact_no']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Login Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($resortData['username']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Login Password *</label>
                    <input type="text" name="password" value="<?php echo htmlspecialchars($resortData['password']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adult Entrance Fee *</label>
                        <input type="number" name="adultEntranceFee" value="<?php echo $resortData['adultEntranceFee']; ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kids Entrance Fee *</label>
                        <input type="number" name="kidsEntranceFee" value="<?php echo $resortData['kidsEntranceFee']; ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>