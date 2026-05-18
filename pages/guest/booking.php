<?php
$resortId = isset($_GET['resort']) ? (int)$_GET['resort'] : (isset($_POST['resortid']) ? (int)$_POST['resortid'] : 0);
$guestId = $_SESSION['user_reg'] ?? null;

$guestData = null;
if($guestId) {
    $guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
    $guest->execute([$guestId]);
    $guestData = $guest->fetch();
}

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if(!$resortData) {
    header('Location: index.php?page=resorts');
    exit;
}

$rooms = $conn->prepare("SELECT * FROM tb_resort_room WHERE resortid = ? AND room_status = 'Available'");
$rooms->execute([$resortId]);

$roomImages = $conn->prepare("SELECT resort_room_id, file_name FROM images WHERE resort_room_id IN (SELECT resort_room_id FROM tb_resort_room WHERE resortid = ?)");
$roomImages->execute([$resortId]);
$roomImageMap = [];
while($ri = $roomImages->fetch()) {
    $roomImageMap[$ri['resort_room_id']][] = $ri['file_name'];
}

$amenities = $conn->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ?");
$amenities->execute([$resortId]);

$img = $conn->prepare("SELECT file_name FROM images WHERE resortid = ? LIMIT 1");
$img->execute([$resortId]);
$image = $img->fetch();
?>

<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="?page=resorts" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left"></i> Back to Resorts
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Resort Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-64 bg-gradient-to-r from-purple-500 via-pink-500 to-emerald-500 flex items-center justify-center">
                    <?php if($image['file_name']): ?>
                    <img src="uploads_flow/<?php echo htmlspecialchars($image['file_name']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                    <i class="bi bi-water-wave text-white text-5xl"></i>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($resortData['resortname']); ?></h1>
                    <p class="text-gray-500 mt-2 flex items-center gap-2">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resortData['resortaddress']); ?>
                    </p>
                    <p class="text-gray-500 flex items-center gap-2">
                        <i class="bi bi-building"></i> <?php echo htmlspecialchars($resortData['mun']); ?>
                    </p>
                    
                    <!-- Entrance Fees -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Entrance Fees</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-purple-50 rounded-xl p-4">
                                <p class="text-sm text-purple-600">Adult</p>
                                <p class="text-2xl font-bold text-purple-600">₱<?php echo number_format($resortData['adultEntranceFee'], 0); ?></p>
                            </div>
                            <div class="bg-emerald-50 rounded-xl p-4">
                                <p class="text-sm text-emerald-600">Kids</p>
                                <p class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($resortData['kidsEntranceFee'], 0); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Book Your Stay</h2>
                
                <form id="bookingForm" method="POST" action="index.php?page=cart">
                    <input type="hidden" name="resortid" value="<?php echo $resortId; ?>">
                    <input type="hidden" name="add_to_cart" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                        <input type="date" name="checkindate" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                        <input type="date" name="checkoutdate" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Room (Optional)</label>
                        <select name="resort_room_id" id="roomSelect" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">No room selected</option>
                            <?php while($room = $rooms->fetch()): ?>
                            <option value="<?php echo $room['resort_room_id']; ?>" data-price="<?php echo $room['room_price']; ?>" data-images='<?php echo htmlspecialchars(json_encode($roomImageMap[$room['resort_room_id']] ?? []), ENT_QUOTES); ?>'>
                                <?php echo htmlspecialchars($room['room_name']); ?> - ₱<?php echo number_format($room['room_price'], 0); ?> (Capacity: <?php echo $room['room_capacity']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <div id="roomPreview" class="mt-3 hidden">
                            <div class="relative group">
                                <img id="roomPreviewImg" class="w-full h-40 object-cover rounded-xl cursor-pointer">
                                <button id="prevImageBtn" type="button" class="absolute left-1 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hidden"><i class="bi bi-chevron-left text-sm"></i></button>
                                <button id="nextImageBtn" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hidden"><i class="bi bi-chevron-right text-sm"></i></button>
                                <div id="imageCounter" class="absolute bottom-1 right-1 bg-black/50 text-white text-xs px-2 py-0.5 rounded hidden"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Adults</label>
                        <input type="number" name="num_adults" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" min="1" value="1" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Kids</label>
                        <input type="number" name="num_kids" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" min="0" value="0">
                    </div>
                    
                    
                    
                    <!-- Total -->
                    <div class="mb-4 pt-4 border-t border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Amount:</span>
                            <span id="totalAmount" class="text-2xl font-bold text-purple-600">₱0.00</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="bi bi-cart-plus"></i> Add to Bookings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="imageLightbox" class="fixed inset-0 bg-black/80 z-[100] hidden flex items-center justify-center">
    <button id="lightboxClose" type="button" class="absolute top-4 right-4 w-10 h-10 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-xl z-10"><i class="bi bi-x-lg"></i></button>
    <button id="lightboxPrev" type="button" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-2xl z-10 hidden"><i class="bi bi-chevron-left"></i></button>
    <button id="lightboxNext" type="button" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-2xl z-10 hidden"><i class="bi bi-chevron-right"></i></button>
    <div id="lightboxCounter" class="absolute top-4 left-4 bg-black/40 text-white text-sm px-3 py-1 rounded-full z-10 hidden"></div>
    <img id="lightboxImg" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg">
</div>

<script>
$(document).ready(function() {
    const adultFee = <?php echo $resortData['adultEntranceFee']; ?>;
    const kidsFee = <?php echo $resortData['kidsEntranceFee']; ?>;
    
    function calculateTotal() {
        let total = 0;
        const adults = parseInt($('input[name="num_adults"]').val()) || 0;
        const kids = parseInt($('input[name="num_kids"]').val()) || 0;
        
        total += adults * adultFee;
        total += kids * kidsFee;
        
        const roomSelect = $('select[name="resort_room_id"]');
        const selectedRoom = roomSelect.find('option:selected');
        if(roomSelect.val() && selectedRoom.data('price')) {
            const checkin = new Date($('input[name="checkindate"]').val());
            const checkout = new Date($('input[name="checkoutdate"]').val());
            let numDays = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
            if(numDays < 1) numDays = 1;
            total += parseInt(selectedRoom.data('price')) * numDays;
        }
        
        $('.amenity-checkbox:checked').each(function() {
            const price = $(this).data('price');
            const quantity = $(this).closest('.flex').find('.amenity-quantity').val() || 1;
            total += price * parseInt(quantity);
        });
        
        $('#totalAmount').text('₱' + total.toLocaleString());
        
        $('input[name="total_amount"]').remove();
        $('<input>').attr({type: 'hidden', name: 'total_amount', value: total}).appendTo('#bookingForm');
    }
    
    $('input[name="num_adults"], input[name="num_kids"], select[name="resort_room_id"], input[name="checkindate"], input[name="checkoutdate"]').on('change keyup', calculateTotal);
    
    calculateTotal();
    
    // Room image gallery
    let currentImageIndex = 0;
    let currentImages = [];

    function showRoomImage(index) {
        if(!currentImages.length) return;
        const img = $('#roomPreviewImg');
        const counter = $('#imageCounter');
        const prevBtn = $('#prevImageBtn');
        const nextBtn = $('#nextImageBtn');
        img.attr('src', 'uploads_flow/' + currentImages[index]);
        counter.text((index + 1) + '/' + currentImages.length);
        prevBtn.toggle(index > 0);
        nextBtn.toggle(index < currentImages.length - 1);
    }

    $('#prevImageBtn').on('click', function() {
        if(currentImageIndex > 0) {
            currentImageIndex--;
            showRoomImage(currentImageIndex);
        }
    });

    $('#nextImageBtn').on('click', function() {
        if(currentImageIndex < currentImages.length - 1) {
            currentImageIndex++;
            showRoomImage(currentImageIndex);
        }
    });

    $('select[name="resort_room_id"]').on('change', function() {
        const selected = $(this).find('option:selected');
        const preview = $('#roomPreview');
        const counter = $('#imageCounter');
        currentImageIndex = 0;

        try {
            currentImages = selected.data('images') || [];
        } catch(e) {
            currentImages = [];
        }

        if(currentImages.length > 0) {
            showRoomImage(0);
            counter.toggleClass('hidden', currentImages.length <= 1);
            preview.removeClass('hidden');
        } else {
            preview.addClass('hidden');
        }
    });

    // Lightbox
    function openLightbox(index) {
        currentImageIndex = index;
        const lightbox = $('#imageLightbox');
        const img = $('#lightboxImg');
        const prev = $('#lightboxPrev');
        const next = $('#lightboxNext');
        const counter = $('#lightboxCounter');
        img.attr('src', 'uploads_flow/' + currentImages[index]);
        prev.toggle(currentImages.length > 1 && index > 0);
        next.toggle(currentImages.length > 1 && index < currentImages.length - 1);
        counter.text((index + 1) + '/' + currentImages.length).toggleClass('hidden', currentImages.length <= 1);
        lightbox.removeClass('hidden');
        document.body.style.overflow = 'hidden';
    }

    $('#roomPreviewImg').on('click', function() {
        if(currentImages.length > 0) openLightbox(currentImageIndex);
    });

    $('#lightboxClose, #imageLightbox').on('click', function(e) {
        if(e.target === this || $(e.target).hasClass('bi-x-lg')) {
            $('#imageLightbox').addClass('hidden');
            document.body.style.overflow = '';
        }
    });

    $('#lightboxPrev').on('click', function() {
        if(currentImageIndex > 0) {
            currentImageIndex--;
            openLightbox(currentImageIndex);
        }
    });

    $('#lightboxNext').on('click', function() {
        if(currentImageIndex < currentImages.length - 1) {
            currentImageIndex++;
            openLightbox(currentImageIndex);
        }
    });

    $(document).on('keydown', function(e) {
        if(!$('#imageLightbox').hasClass('hidden')) {
            if(e.key === 'Escape') {
                $('#imageLightbox').addClass('hidden');
                document.body.style.overflow = '';
            } else if(e.key === 'ArrowLeft') { $('#lightboxPrev').click(); }
            else if(e.key === 'ArrowRight') { $('#lightboxNext').click(); }
        }
    });
    
    // Set min dates
    const today = new Date().toISOString().split('T')[0];
    $('input[name="checkindate"]').attr('min', today);
    $('input[name="checkoutdate"]').attr('min', today);
    
    $('input[name="checkindate"]').on('change', function() {
        const checkin = $(this).val();
        if(checkin) {
            $('input[name="checkoutdate"]').attr('min', checkin);
        }
    });
});
</script>