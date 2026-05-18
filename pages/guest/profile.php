<?php
$guestId = $_SESSION['user_reg'];
$guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
$guest->execute([$guestId]);
$guestData = $guest->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $middlename = $_POST['middlename'];
    $contactno = $_POST['contactno'];
    $address = $_POST['address'];

    
    $update = $conn->prepare("UPDATE tb_guest SET FirstName = ?, LastName = ?, MiddleName = ?, ContactNo = ?, Address = ? WHERE guest_id = ?");
    $update->execute([$firstName, $lastName, $middlename, $contactno, $address, $guestId]);
    $success = "Profile updated successfully!";
    
    $guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
    $guest->execute([$guestId]);
    $guestData = $guest->fetch();
}
?>

<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Profile</h1>
            <p class="text-gray-500">Manage your personal information</p>
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($guestData['FirstName']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($guestData['LastName']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                    <input type="text" name="middlename" value="<?php echo htmlspecialchars($guestData['MiddleName']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                    <input type="tel" name="contactno" value="<?php echo htmlspecialchars($guestData['ContactNo']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                    <textarea name="address" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="2" required><?php echo htmlspecialchars($guestData['Address']); ?></textarea>
                </div>
                
                <div class="mb-5 p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">
                        <i class="bi bi-key me-1"></i>
                        Username: <strong><?php echo htmlspecialchars($guestData['Username']); ?></strong>
                    </p>
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>