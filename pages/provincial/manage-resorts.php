<?php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_flags'])) {
    $resortid = $_POST['resortid'];
    $isFeatured = isset($_POST['isFeatured']) ? 1 : 0;
    $isTopItem = isset($_POST['isTopItem']) ? 1 : 0;
    $isBestSeller = isset($_POST['isBestSeller']) ? 1 : 0;
    $isPromoDeals = isset($_POST['isPromoDeals']) ? 1 : 0;
    $isOnSale = isset($_POST['isOnSale']) ? 1 : 0;
    
    $update = $conn->prepare("UPDATE tb_resort SET isFeatured = ?, isTopItem = ?, isBestSeller = ?, isPromoDeals = ?, isOnSale = ? WHERE resortid = ?");
    $update->execute([$isFeatured, $isTopItem, $isBestSeller, $isPromoDeals, $isOnSale, $resortid]);
    $success = "Resort flags updated!";
}

$search = $_GET['search'] ?? '';
$munFilter = $_GET['mun'] ?? 'all';

$sql = "SELECT r.*, m.mun, m.district FROM tb_resort r JOIN tb_municipality m ON r.mun = m.mun WHERE 1=1";
$params = [];

if($search) {
    $sql .= " AND (r.resortname LIKE ? OR m.mun LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if($munFilter != 'all') {
    $sql .= " AND r.mun = ?";
    $params[] = $munFilter;
}
$sql .= " ORDER BY r.resortname";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$allResorts = $stmt->fetchAll();
$hasResults = count($allResorts) > 0;

$munList = $conn->query("SELECT DISTINCT mun FROM tb_resort ORDER BY mun");
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Resorts</h1>
            <p class="text-gray-500">Set featured status, best sellers, and promotions</p>
        </div>
    </div>

    <?php if(isset($success)): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-check-circle text-emerald-600"></i>
        <span class="text-emerald-700"><?php echo $success; ?></span>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="index.php" class="flex flex-wrap gap-3">
            <input type="hidden" name="page" value="provincial-resorts">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by resort or municipality..." class="px-4 py-2 border border-gray-200 rounded-xl text-sm flex-1 min-w-48">
            <select name="mun" class="px-4 py-2 border border-gray-200 rounded-xl text-sm">
                <option value="all">All Municipalities</option>
                <?php while($m = $munList->fetch()): ?>
                <option value="<?php echo $m['mun']; ?>" <?php echo $munFilter == $m['mun'] ? 'selected' : ''; ?>><?php echo $m['mun']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl text-sm font-medium">
                <i class="bi bi-search me-1"></i> Search
            </button>
            <?php if($search || $munFilter != 'all'): ?>
            <a href="?page=provincial-resorts" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition flex items-center gap-1">
                <i class="bi bi-x-lg"></i> Clear
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Resort</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Municipality</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">District</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Featured</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Best Seller</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Top Item</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Promo</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">On Sale</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if($hasResults): ?>
                    <?php foreach($allResorts as $resort): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($resort['resortname']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo htmlspecialchars($resort['mun']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($resort['district']); ?></p>
                        </td>
                        <form method="POST">
                        <input type="hidden" name="update_flags" value="1">
                        <input type="hidden" name="resortid" value="<?php echo $resort['resortid']; ?>">
                        <td class="px-5 py-4 text-center">
                            <input type="checkbox" name="isFeatured" value="1" <?php echo $resort['isFeatured'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="w-4 h-4 text-teal-600 rounded">
                        </td>
                        <td class="px-5 py-4 text-center">
                            <input type="checkbox" name="isBestSeller" value="1" <?php echo $resort['isBestSeller'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="w-4 h-4 text-teal-600 rounded">
                        </td>
                        <td class="px-5 py-4 text-center">
                            <input type="checkbox" name="isTopItem" value="1" <?php echo $resort['isTopItem'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="w-4 h-4 text-teal-600 rounded">
                        </td>
                        <td class="px-5 py-4 text-center">
                            <input type="checkbox" name="isPromoDeals" value="1" <?php echo $resort['isPromoDeals'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="w-4 h-4 text-teal-600 rounded">
                        </td>
                        <td class="px-5 py-4 text-center">
                            <input type="checkbox" name="isOnSale" value="1" <?php echo $resort['isOnSale'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="w-4 h-4 text-teal-600 rounded">
                        </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="bi bi-search text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No resorts found</p>
                            <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>