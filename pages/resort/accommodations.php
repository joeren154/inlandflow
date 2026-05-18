<?php
$resortId = $_SESSION['user_reg'];

// Get resort details
$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

// Update entrance fees
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_fees'])) {
    $adultFee = $_POST['adult_fee'];
    $kidsFee = $_POST['kids_fee'];
    
    $update = $conn->prepare("UPDATE tb_resort SET adultEntranceFee = ?, kidsEntranceFee = ? WHERE resortid = ?");
    $update->execute([$adultFee, $kidsFee, $resortId]);
    $success = "Entrance fees updated successfully!";
    
    // Refresh resort data
    $resort->execute([$resortId]);
    $resortData = $resort->fetch();
}

// Update resort status flags
if(isset($_POST['update_flags'])) {
    $isFeatured = isset($_POST['isFeatured']) ? 1 : 0;
    $isTopItem = isset($_POST['isTopItem']) ? 1 : 0;
    $isBestSeller = isset($_POST['isBestSeller']) ? 1 : 0;
    $isPromoDeals = isset($_POST['isPromoDeals']) ? 1 : 0;
    $isOnSale = isset($_POST['isOnSale']) ? 1 : 0;
    
    $update = $conn->prepare("UPDATE tb_resort SET isFeatured = ?, isTopItem = ?, isBestSeller = ?, isPromoDeals = ?, isOnSale = ? WHERE resortid = ?");
    $update->execute([$isFeatured, $isTopItem, $isBestSeller, $isPromoDeals, $isOnSale, $resortId]);
    $success = "Resort flags updated successfully!";
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 pt-20">
    <div class="container mx-auto px-6 py-8">
        <div class="mb-8" data-aos="fade-up">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                Accommodations Management
            </h1>
            <p class="text-slate-600 mt-2">Manage entrance fees, rooms, and amenities</p>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Entrance Fees -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Entrance Fees</h2>
                <form method="POST">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label fw-semibold">Adult Entrance Fee (₱)</label>
                            <input type="number" name="adult_fee" class="form-control rounded-xl" 
                                   value="<?php echo $resortData['adultEntranceFee']; ?>" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Kids Entrance Fee (₱)</label>
                            <input type="number" name="kids_fee" class="form-control rounded-xl" 
                                   value="<?php echo $resortData['kidsEntranceFee']; ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_fees" class="btn-gradient w-full py-2 rounded-xl">
                        Update Fees
                    </button>
                </form>
            </div>
            
            <!-- Resort Flags -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Resort Promotions</h2>
                <form method="POST">
                    <div class="space-y-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isFeatured" id="isFeatured" 
                                   <?php echo $resortData['isFeatured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isFeatured">
                                Featured Resort (Shown on homepage)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isTopItem" id="isTopItem" 
                                   <?php echo $resortData['isTopItem'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isTopItem">
                                Top Item (Priority listing)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isBestSeller" id="isBestSeller" 
                                   <?php echo $resortData['isBestSeller'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isBestSeller">
                                Best Seller
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isPromoDeals" id="isPromoDeals" 
                                   <?php echo $resortData['isPromoDeals'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isPromoDeals">
                                Promo Deals
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isOnSale" id="isOnSale" 
                                   <?php echo $resortData['isOnSale'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isOnSale">
                                On Sale
                            </label>
                        </div>
                    </div>
                    <button type="submit" name="update_flags" class="btn-gradient w-full mt-4 py-2 rounded-xl">
                        Update Promotions
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Quick Links to Rooms and Amenities -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-500 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Manage Rooms</h3>
                        <p class="text-white/80 mt-1">Add, edit, or remove resort rooms</p>
                        <button onclick="location.href='?page=resort-rooms'" 
                                class="mt-4 px-4 py-2 bg-white/20 rounded-lg hover:bg-white/30 transition">
                            Go to Rooms <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                    <i class="bi bi-door-open text-5xl text-white/30"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Manage Amenities</h3>
                        <p class="text-white/80 mt-1">Add, edit, or remove add-on amenities</p>
                        <button onclick="location.href='?page=resort-amenities'" 
                                class="mt-4 px-4 py-2 bg-white/20 rounded-lg hover:bg-white/30 transition">
                            Go to Amenities <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                    <i class="bi bi-grid-3x3-gap-fill text-5xl text-white/30"></i>
                </div>
            </div>
        </div>
    </div>
</div>