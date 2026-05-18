<?php
$guestId = $_SESSION['user_reg'];

$guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
$guest->execute([$guestId]);
$guestData = $guest->fetch();

if (!$guestData) {
    header('Location: index.php?page=login');
    exit;
}

$search = $_GET['search'] ?? '';
$districtFilter = $_GET['district'] ?? 'all';

$sql = "SELECT * FROM tb_resort WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND resortname LIKE ?";
    $params[] = "%$search%";
}
if ($districtFilter != 'all') {
    $sql .= " AND district = ?";
    $params[] = $districtFilter;
}
$sql .= " ORDER BY resortname";

$resorts = $conn->prepare($sql);
$resorts->execute($params);

$districts = $conn->query("SELECT DISTINCT district FROM tb_resort WHERE district IS NOT NULL ORDER BY district")->fetchAll();

$cartCount = $conn->prepare("SELECT COUNT(*) as count FROM tb_cart WHERE guest_id = ? AND cart_status = 'Pending'");
$cartCount->execute([$guestId]);
$cartNum = $cartCount->fetch()['count'];

// Fetch gallery images for all displayed resorts
$allGallery = $conn->query("SELECT resortid, file_name FROM images WHERE resort_room_id IS NULL ORDER BY id");
$resortGalleryMap = [];
while($gi = $allGallery->fetch()) {
    $resortGalleryMap[$gi['resortid']][] = $gi['file_name'];
}
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Browse Resorts</h1>
            <p class="text-gray-500">Find your perfect getaway in Iloilo Province</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="?page=guest-cart" class="relative px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center gap-2">
                <i class="bi bi-cart"></i> My Cart
                <?php if($cartNum > 0): ?>
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-purple-600 text-white text-xs rounded-full flex items-center justify-center"><?php echo $cartNum; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="hidden" name="page" value="guest-resorts">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search resorts..." class="flex-1 min-w-48 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            <select name="district" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="all">All Districts</option>
                <?php foreach($districts as $d): ?>
                <option value="<?php echo $d['district']; ?>" <?php echo $districtFilter == $d['district'] ? 'selected' : ''; ?>><?php echo $d['district']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium">
                Search
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php while($resort = $resorts->fetch()): ?>
        <?php 
            $resGallery = $resortGalleryMap[$resort['resortid']] ?? [];
            $firstImg = $resGallery[0] ?? null;
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
            <div class="h-40 bg-gradient-to-r from-purple-500 to-emerald-500 flex items-center justify-center relative overflow-hidden cursor-pointer"
                 onclick="openResortGallery(<?php echo htmlspecialchars(json_encode($resGallery), ENT_QUOTES); ?>)">
                <?php if($firstImg): ?>
                <img src="uploads_flow/<?php echo htmlspecialchars($firstImg); ?>" class="w-full h-full object-cover absolute inset-0">
                <?php else: ?>
                <i class="bi bi-water-wave text-white text-3xl"></i>
                <?php endif; ?>
                <?php if($resort['isFeatured']): ?>
                <span class="absolute top-3 right-3 px-2 py-1 bg-amber-500 text-white text-xs rounded-full font-medium z-10">
                    <i class="bi bi-star-fill"></i> Featured
                </span>
                <?php endif; ?>
                <?php if(count($resGallery) > 0): ?>
                <div class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded z-10"><i class="bi bi-images"></i> <?php echo count($resGallery); ?></div>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($resort['resortname']); ?></h3>
                <p class="text-sm text-gray-500 mb-3 flex items-center gap-1">
                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resort['resortaddress']); ?>
                </p>
                
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2 py-1 bg-purple-100 text-purple-600 text-xs rounded-full"><?php echo htmlspecialchars($resort['district']); ?></span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded-full"><?php echo htmlspecialchars($resort['mun']); ?></span>
                </div>
                
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs text-gray-400">Adult</p>
                        <p class="font-semibold text-purple-600">₱<?php echo number_format($resort['adultEntranceFee'], 0); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Kids</p>
                        <p class="font-semibold text-emerald-600">₱<?php echo number_format($resort['kidsEntranceFee'], 0); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Contact</p>
                        <p class="font-medium text-gray-600"><?php echo htmlspecialchars($resort['contact_no']); ?></p>
                    </div>
                </div>
                
                <a href="?page=resort-detail&id=<?php echo $resort['resortid']; ?>" class="block w-full py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition text-center">
                    View & Book
                </a>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php if($resorts->rowCount() == 0): ?>
        <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-search text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No resorts found</h3>
            <p class="text-gray-500">Try adjusting your search criteria</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Resort Detail Modal -->
<div id="resortModal" class="fixed inset-0 bg-black/50 z-50 hidden overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen p-4 pt-20">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[80vh] overflow-y-auto">
            <div id="resortContent" class="p-6">
                <div class="text-center">
                    <i class="bi bi-arrow-repeat animate-spin text-2xl text-purple-600"></i>
                    <p class="mt-2 text-gray-500">Loading resort details...</p>
                </div>
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
function viewResort(resortId) {
    document.getElementById('resortModal').classList.remove('hidden');
    $.post('api/get-resort-details.php', {resortid: resortId}, function(data) {
        document.getElementById('resortContent').innerHTML = data;
    });
}

function closeResortModal() {
    document.getElementById('resortModal').classList.add('hidden');
}

document.getElementById('resortModal').addEventListener('click', function(e) {
    if(e.target === this || e.target.closest('.close-modal')) {
        closeResortModal();
    }
});

// Lightbox
let lbImages = [];
let lbIndex = 0;

function openResortGallery(images) {
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