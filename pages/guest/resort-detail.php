<?php
$resortId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$guestId = $_SESSION['user_reg'] ?? 0;

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if(!$resortData) {
    header('Location: index.php?page=resorts');
    exit;
}

// Gallery images
$gallery = $conn->prepare("SELECT file_name FROM images WHERE resortid = ? AND resort_room_id IS NULL");
$gallery->execute([$resortId]);
$images = $gallery->fetchAll();

// Location
$location = $conn->prepare("SELECT * FROM tb_location WHERE resortid = ?");
$location->execute([$resortId]);
$locData = $location->fetch();

// Amenities
$amenities = $conn->prepare("SELECT * FROM tb_resort_amenities WHERE resortid = ?");
$amenities->execute([$resortId]);

// Rooms
$checkDate = $_GET['checkdate'] ?? date('Y-m-d');
$rooms = $conn->prepare("SELECT * FROM tb_resort_room WHERE resortid = ?");
$rooms->execute([$resortId]);

// Ratings
$rStatus = "Reviewed";
$ratingsStmt = $conn->prepare("SELECT po.ratings, po.rating_comment, g.Username, g.FirstName, g.LastName FROM tb_placed_order po JOIN tb_cart c ON po.cart_id = c.cart_id JOIN tb_guest g ON c.guest_id = g.guest_id WHERE c.resortid = ? AND po.reservation_status = ? AND po.ratings > 0");
$ratingsStmt->execute([$resortId, $rStatus]);

$totalRate = 0;
$countRate = 0;
$ratingsList = [];
while($r = $ratingsStmt->fetch()) {
    $totalRate += $r['ratings'];
    $countRate++;
    $ratingsList[] = $r;
}
$averageRate = $countRate > 0 ? round($totalRate / $countRate, 2) : 0;
?>

<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="?page=resorts" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left"></i> Back to Resorts
        </a>
    </div>

    <!-- Image Carousel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="relative h-80 md:h-[500px] overflow-hidden group" id="galleryCarousel">
            <?php if(count($images) > 0): ?>
                <?php foreach($images as $idx => $img): ?>
                <div class="carousel-slide absolute inset-0 transition-opacity duration-500 <?php echo $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>">
                    <img src="uploads_flow/<?php echo htmlspecialchars($img['file_name']); ?>" class="w-full h-full object-cover cursor-pointer" onclick="openDetailGallery(<?php echo $idx; ?>)">
                </div>
                <?php endforeach; ?>
                <!-- Carousel Controls -->
                <?php if(count($images) > 1): ?>
                <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition z-20">
                    <i class="bi bi-chevron-left text-xl"></i>
                </button>
                <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition z-20">
                    <i class="bi bi-chevron-right text-xl"></i>
                </button>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-20">
                    <?php foreach($images as $idx => $img): ?>
                    <button onclick="goToSlide(<?php echo $idx; ?>)" class="w-3 h-3 rounded-full transition <?php echo $idx === 0 ? 'bg-white' : 'bg-white/50'; ?>" id="dot-<?php echo $idx; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="w-full h-full bg-gradient-to-r from-purple-500 to-emerald-500 flex items-center justify-center">
                    <i class="bi bi-water-wave text-white text-6xl"></i>
                </div>
            <?php endif; ?>
            <!-- Overlay Title -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent z-10 flex items-end">
                <div class="p-6 md:p-8">
                    <h1 class="text-3xl md:text-5xl font-bold text-white mb-2"><?php echo htmlspecialchars($resortData['resortname']); ?></h1>
                    <p class="text-white/90 text-lg flex items-center gap-2">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resortData['resortaddress']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Entrance Fees -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-cash-coin text-purple-500"></i> Entrance Fees
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-purple-50 rounded-xl p-5 text-center">
                        <p class="text-sm text-purple-600 mb-1">Adult</p>
                        <p class="text-3xl font-bold text-purple-600">₱<?php echo number_format($resortData['adultEntranceFee'], 0); ?></p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-5 text-center">
                        <p class="text-sm text-emerald-600 mb-1">Kids</p>
                        <p class="text-3xl font-bold text-emerald-600">₱<?php echo number_format($resortData['kidsEntranceFee'], 0); ?></p>
                    </div>
                </div>
            </div>

            <!-- Rooms & Availability -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-door-open text-purple-500"></i> Accommodations
                    </h2>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-500">Check Availability:</label>
                        <input type="date" id="roomCheckDate" value="<?php echo $checkDate; ?>" class="px-3 py-2 border border-gray-200 rounded-xl text-sm" onchange="checkAvailability()">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Photo</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Room Name</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Capacity</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Price</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="roomTableBody">
                            <?php 
                            $rooms->execute([$resortId]);
                            while($room = $rooms->fetch()): 
                                // Check availability for the selected date
                                $availSql = $conn->prepare("SELECT COUNT(*) FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE c.resort_room_id = ? AND c.checkindate = ? AND po.reservation_status IN ('Approved','PaymentApproval')");
                                $availSql->execute([$room['resort_room_id'], $checkDate]);
                                $isBooked = $availSql->fetchColumn() > 0;
                                
                                $addOnSql = $conn->prepare("SELECT COUNT(*) FROM tb_add_on_details aod JOIN tb_placed_order po ON aod.po_id = po.po_id JOIN tb_cart c ON po.cart_id = c.cart_id WHERE aod.resort_room_id = ? AND c.checkindate = ? AND po.reservation_status IN ('Approved','PaymentApproval')");
                                $addOnSql->execute([$room['resort_room_id'], $checkDate]);
                                $isAddOnBooked = $addOnSql->fetchColumn() > 0;
                                
                                $isAvailable = !$isBooked && !$isAddOnBooked && $room['room_status'] === 'Available';

                                $roomImg = $conn->prepare("SELECT file_name FROM images WHERE resort_room_id = ?");
                                $roomImg->execute([$room['resort_room_id']]);
                                $roomImgData = $roomImg->fetchAll();
                            ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 cursor-pointer" onclick='openRoomGallery(<?php echo json_encode(array_column($roomImgData, 'file_name')); ?>)'>
                                        <?php if(!empty($roomImgData)): ?>
                                        <img src="uploads_flow/<?php echo htmlspecialchars($roomImgData[0]['file_name']); ?>" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-300\'><i class=\'bi bi-image\'></i></div>'">
                                        <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="bi bi-image"></i></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($room['room_name']); ?></div>
                                    <?php if (!empty($room['room_description'])): ?>
                                    <div class="text-xs text-gray-500 mt-0.5 max-w-[200px] truncate" title="<?php echo htmlspecialchars($room['room_description'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($room['room_description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600"><?php echo $room['room_capacity']; ?> persons</td>
                                <td class="px-4 py-3 text-center font-semibold text-purple-600">₱<?php echo number_format($room['room_price'], 0); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($isAvailable): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                        <i class="bi bi-check-circle"></i> Available
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        <i class="bi bi-x-circle"></i> Not Available
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-400 mt-2">* Availability as of <?php echo date('M j, Y', strtotime($checkDate)); ?></p>
            </div>

            <!-- Amenities -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-gear text-purple-500"></i> Amenities
                </h2>
                <?php if($amenities->rowCount() > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php while($amenity = $amenities->fetch()): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="text-gray-700"><?php echo htmlspecialchars($amenity['amenity_name']); ?></span>
                        <span class="font-semibold text-purple-600">₱<?php echo number_format($amenity['amenity_price'], 0); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">No amenities listed yet.</p>
                <?php endif; ?>
            </div>

            <!-- Ratings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-star-fill text-amber-500"></i> Resort Rating
                </h2>
                <div class="flex items-center gap-4 mb-6">
                    <div class="text-5xl font-bold text-gray-800"><?php echo $averageRate > 0 ? number_format($averageRate, 2) : 'N/A'; ?></div>
                    <div>
                        <div class="flex gap-1 mb-1">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star-fill text-xl <?php echo $i <= round($averageRate) ? 'text-amber-400' : 'text-gray-200'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-sm text-gray-500"><?php echo $countRate; ?> review(s)</p>
                    </div>
                </div>
                <?php if(count($ratingsList) > 0): ?>
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    <?php foreach($ratingsList as $rating): ?>
                    <div class="border-b border-gray-100 pb-4 last:border-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($rating['Username'] ?: $rating['FirstName']); ?></span>
                            <div class="flex gap-0.5">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star-fill text-xs <?php echo $i <= $rating['ratings'] ? 'text-amber-400' : 'text-gray-200'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($rating['rating_comment'] ?: 'No comment'); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">No ratings yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Book & Location -->
        <div class="space-y-6">
            <!-- Book Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Book Now</h2>
                <a href="?page=booking&resort=<?php echo $resortId; ?>" class="block w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition text-center mb-4">
                    <i class="bi bi-calendar-check"></i> Make a Reservation
                </a>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="bi bi-telephone text-purple-500"></i>
                        <span><?php echo htmlspecialchars($resortData['contact_no']); ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="bi bi-building text-purple-500"></i>
                        <span><?php echo htmlspecialchars($resortData['mun']); ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="bi bi-geo text-purple-500"></i>
                        <span><?php echo htmlspecialchars($resortData['district']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <?php if($locData): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-pin-map-fill text-purple-500"></i> Location
                </h2>
                <div class="rounded-xl overflow-hidden h-48 bg-gray-100 mb-3">
                    <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                        src="https://maps.google.com/maps?q=<?php echo $locData['lat']; ?>,<?php echo $locData['lon']; ?>&hl=es;z=14&amp;output=embed">
                    </iframe>
                </div>
                <p class="text-sm text-gray-500">Lat: <?php echo $locData['lat']; ?>, Lon: <?php echo $locData['lon']; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="imageLightbox" class="fixed inset-0 bg-black/80 z-[100] hidden flex items-center justify-center">
    <button id="lightboxClose" type="button" class="absolute top-4 right-4 w-10 h-10 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-xl z-10"><i class="bi bi-x-lg"></i></button>
    <button id="lightboxPrev" type="button" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-2xl z-10 hidden"><i class="bi bi-chevron-left"></i></button>
    <button id="lightboxNext" type="button" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center text-2xl z-10 hidden"><i class="bi bi-chevron-right"></i></button>
    <div id="lightboxCounter" class="absolute top-4 left-4 bg-black/40 text-white text-sm px-3 py-1 rounded-full z-10 hidden"></div>
    <img id="lightboxImg" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg">
</div>

<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
const totalSlides = slides.length;

function updateSlides() {
    slides.forEach((slide, idx) => {
        if(idx === currentSlide) {
            slide.classList.remove('opacity-0', 'z-0');
            slide.classList.add('opacity-100', 'z-10');
        } else {
            slide.classList.remove('opacity-100', 'z-10');
            slide.classList.add('opacity-0', 'z-0');
        }
    });
    // Update dots
    document.querySelectorAll('[id^="dot-"]').forEach((dot, idx) => {
        if(idx === currentSlide) {
            dot.classList.remove('bg-white/50');
            dot.classList.add('bg-white');
        } else {
            dot.classList.remove('bg-white');
            dot.classList.add('bg-white/50');
        }
    });
}

function nextSlide() {
    if(totalSlides === 0) return;
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlides();
}

function prevSlide() {
    if(totalSlides === 0) return;
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlides();
}

function goToSlide(idx) {
    currentSlide = idx;
    updateSlides();
}

// Auto-advance carousel
if(totalSlides > 1) {
    setInterval(nextSlide, 5000);
}

function checkAvailability() {
    const date = document.getElementById('roomCheckDate').value;
    window.location.href = '?page=resort-detail&id=<?php echo $resortId; ?>&checkdate=' + date;
}

// Lightbox
let lbImages = <?php echo json_encode(array_column($images, 'file_name')); ?>;
let lbIndex = 0;

function openDetailGallery(index) {
    lbIndex = index;
    openLightbox(lbImages, lbIndex);
}

function openRoomGallery(images) {
    openLightbox(images, 0);
}

function openLightbox(images, index) {
    lbImages = images;
    lbIndex = index;
    const lb = document.getElementById('imageLightbox');
    const img = document.getElementById('lightboxImg');
    const prev = document.getElementById('lightboxPrev');
    const next = document.getElementById('lightboxNext');
    const counter = document.getElementById('lightboxCounter');
    if(!images.length) return;
    img.src = 'uploads_flow/' + images[index];
    prev.classList.toggle('hidden', images.length <= 1 || index <= 0);
    next.classList.toggle('hidden', images.length <= 1 || index >= images.length - 1);
    counter.textContent = (index + 1) + '/' + images.length;
    counter.classList.toggle('hidden', images.length <= 1);
    lb.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

document.getElementById('lightboxClose').addEventListener('click', function() {
    document.getElementById('imageLightbox').classList.add('hidden');
    document.body.style.overflow = '';
});

document.getElementById('imageLightbox').addEventListener('click', function(e) {
    if(e.target === this) {
        document.getElementById('imageLightbox').classList.add('hidden');
        document.body.style.overflow = '';
    }
});

document.getElementById('lightboxPrev').addEventListener('click', function() {
    if(lbIndex > 0) { lbIndex--; openLightbox(lbImages, lbIndex); }
});

document.getElementById('lightboxNext').addEventListener('click', function() {
    if(lbIndex < lbImages.length - 1) { lbIndex++; openLightbox(lbImages, lbIndex); }
});

document.addEventListener('keydown', function(e) {
    if(document.getElementById('imageLightbox').classList.contains('hidden')) return;
    if(e.key === 'Escape') { document.getElementById('imageLightbox').classList.add('hidden'); document.body.style.overflow = ''; }
    else if(e.key === 'ArrowLeft') { document.getElementById('lightboxPrev').click(); }
    else if(e.key === 'ArrowRight') { document.getElementById('lightboxNext').click(); }
});
</script>

