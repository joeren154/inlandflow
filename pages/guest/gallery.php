<?php
$guestId = $_SESSION['user_reg'] ?? 0;

$districtFilter = $_GET['district'] ?? 'all';

$sql = "SELECT r.*, (SELECT file_name FROM images WHERE resortid = r.resortid AND resort_room_id IS NULL LIMIT 1) as first_image,
        (SELECT COUNT(*) FROM images WHERE resortid = r.resortid AND resort_room_id IS NULL) as image_count
        FROM tb_resort r
        WHERE r.isLocated = 1 AND (SELECT COUNT(*) FROM images WHERE resortid = r.resortid AND resort_room_id IS NULL) > 0";
$params = [];

if ($districtFilter != 'all') {
    $sql .= " AND r.district = ?";
    $params[] = $districtFilter;
}
$sql .= " ORDER BY image_count DESC, r.resortname";

$resorts = $conn->prepare($sql);
$resorts->execute($params);

$districts = $conn->query("SELECT DISTINCT district FROM tb_resort WHERE isLocated = 1 AND district IS NOT NULL ORDER BY district")->fetchAll();
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Resort Gallery</h1>
        <p class="text-gray-500">Browse photos of all inland resorts in Iloilo Province</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="hidden" name="page" value="gallery">
            <select name="district" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="all">All Districts</option>
                <?php foreach($districts as $d): ?>
                <option value="<?php echo $d['district']; ?>" <?php echo $districtFilter == $d['district'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['district']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium">
                Filter
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while($resort = $resorts->fetch()): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
            <a href="?page=resort-detail&id=<?php echo $resort['resortid']; ?>" class="block h-56 bg-gray-100 relative overflow-hidden">
                <?php if($resort['first_image']): ?>
                <img src="uploads_flow/<?php echo htmlspecialchars($resort['first_image']); ?>" 
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-r from-purple-500 to-emerald-500\'><i class=\'bi bi-water-wave text-white text-4xl\'></i></div>'">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-purple-500 to-emerald-500">
                    <i class="bi bi-water-wave text-white text-4xl"></i>
                </div>
                <?php endif; ?>
                <span class="absolute top-3 right-3 px-2.5 py-1 bg-black/60 text-white text-xs rounded-full backdrop-blur-sm">
                    <i class="bi bi-images me-1"></i><?php echo $resort['image_count']; ?>
                </span>
            </a>
            <div class="p-4">
                <a href="?page=resort-detail&id=<?php echo $resort['resortid']; ?>" class="font-semibold text-gray-800 hover:text-purple-600 transition"><?php echo htmlspecialchars($resort['resortname']); ?></a>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resort['mun']); ?>
                    </span>
                    <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-600 rounded-full"><?php echo htmlspecialchars($resort['district']); ?></span>
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <span class="text-sm font-semibold text-purple-600">₱<?php echo number_format($resort['adultEntranceFee'], 0); ?></span>
                    <a href="?page=resort-detail&id=<?php echo $resort['resortid']; ?>" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        View Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

        <?php if($resorts->rowCount() == 0): ?>
        <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-images text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No gallery images found</h3>
            <p class="text-gray-500">Resorts haven't uploaded any photos yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>
