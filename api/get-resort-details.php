<?php
require_once 'connection.php';
session_start();

if (!isset($_SESSION['user_reg']) || !isset($_SESSION['type_of_user'])) {
    echo '<div class="text-center p-8"><p class="text-red-500">Please log in to view resort details</p></div>';
    exit;
}

$resortId = $_POST['resortid'] ?? 0;

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<div class="text-center p-8"><p class="text-red-500">Resort not found</p></div>';
    exit;
}

$rooms = $conn->prepare("SELECT * FROM tb_resort_room WHERE resortid = ? AND room_status = 'Available'");
$rooms->execute([$resortId]);

$amenities = $conn->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ?");
$amenities->execute([$resortId]);

$img = $conn->prepare("SELECT file_name FROM images WHERE resortid = ? LIMIT 1");
$img->execute([$resortId]);
$image = $img->fetch();
?>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($resortData['resortname']); ?></h2>
        <p class="text-gray-500 flex items-center gap-1 mt-1">
            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resortData['resortaddress']); ?>
        </p>
        <div class="flex items-center gap-2 mt-2">
            <span class="px-2 py-1 bg-purple-100 text-purple-600 text-xs rounded-full"><?php echo htmlspecialchars($resortData['district']); ?></span>
            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded-full"><?php echo htmlspecialchars($resortData['mun']); ?></span>
        </div>
    </div>
    <button onclick="closeResortModal()" class="text-gray-400 hover:text-gray-600">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <div class="h-48 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-xl flex items-center justify-center">
            <?php if($image['file_name']): ?>
            <img src="uploads_flow/<?php echo htmlspecialchars($image['file_name']); ?>" class="w-full h-full object-cover rounded-xl">
            <?php else: ?>
            <i class="bi bi-water-wave text-white text-4xl"></i>
            <?php endif; ?>
        </div>
    </div>
    <div>
        <h3 class="font-semibold text-gray-800 mb-3">Entrance Fees</h3>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-sm text-purple-600">Adult</p>
                <p class="text-2xl font-bold text-purple-600">₱<?php echo number_format($resortData['adultEntranceFee'], 0); ?></p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4">
                <p class="text-sm text-emerald-600">Kids</p>
                <p class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($resortData['kidsEntranceFee'], 0); ?></p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
            <i class="bi bi-telephone"></i>
            <span><?php echo htmlspecialchars($resortData['contact_no']); ?></span>
        </div>
    </div>
</div>

<?php if($rooms->rowCount() > 0): ?>
<h3 class="font-semibold text-gray-800 mb-3">Available Rooms</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
    <?php while($room = $rooms->fetch()): ?>
    <div class="bg-gray-50 rounded-xl p-3 flex justify-between items-center">
        <div>
            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($room['room_name']); ?></p>
            <p class="text-sm text-gray-500">Capacity: <?php echo $room['room_capacity']; ?> persons</p>
        </div>
        <p class="font-bold text-purple-600">₱<?php echo number_format($room['room_price'], 0); ?></p>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php if($amenities->rowCount() > 0): ?>
<h3 class="font-semibold text-gray-800 mb-3">Amenities</h3>
<div class="flex flex-wrap gap-2 mb-6">
    <?php while($amenity = $amenities->fetch()): ?>
    <span class="px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-700">
        <?php echo htmlspecialchars($amenity['amenity_name']); ?> - ₱<?php echo number_format($amenity['amenity_price'], 0); ?>
    </span>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<form id="bookingForm" method="POST" action="index.php?page=checkout">
    <input type="hidden" name="resortid" value="<?php echo $resortId; ?>">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
            <input type="date" name="checkindate" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
            <input type="date" name="checkoutdate" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Adults</label>
            <input type="number" name="num_adults" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" min="1" value="1" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kids</label>
            <input type="number" name="num_kids" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl" min="0" value="0">
        </div>
    </div>
    
    <input type="hidden" name="total_amount" value="0" id="totalAmountInput">
    
    <div class="mb-4 p-4 bg-gray-50 rounded-xl">
        <div class="flex justify-between items-center">
            <span class="text-gray-600">Total:</span>
            <span class="text-2xl font-bold text-purple-600" id="totalDisplay">₱0</span>
        </div>
    </div>
    
    <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
        Proceed to Checkout
    </button>
</form>

<script>
$(document).ready(function() {
    const adultFee = <?php echo $resortData['adultEntranceFee']; ?>;
    const kidsFee = <?php echo $resortData['kidsEntranceFee']; ?>;
    
    function calculateTotal() {
        const adults = parseInt($('input[name="num_adults"]').val()) || 0;
        const kids = parseInt($('input[name="num_kids"]').val()) || 0;
        const total = adults * adultFee + kids * kidsFee;
        $('#totalDisplay').text('₱' + total.toLocaleString());
        $('#totalAmountInput').val(total);
    }
    
    $('input[name="num_adults"], input[name="num_kids"]').on('change keyup', calculateTotal);
    
    const checkin = $('input[name="checkindate"]');
    const checkout = $('input[name="checkoutdate"]');
    
    checkin.on('change', function() {
        const nextDay = new Date($(this).val());
        nextDay.setDate(nextDay.getDate() + 1);
        checkout.attr('min', nextDay.toISOString().split('T')[0]);
    });
    
    calculateTotal();
});
</script>