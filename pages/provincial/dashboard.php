<?php
$provincialId = $_SESSION['user_reg'];

$municipalities = $conn->query("SELECT id, mun, district FROM tb_municipality ORDER BY mun");

$totalResorts = $conn->query("SELECT COUNT(*) as count FROM tb_resort WHERE isLocated = 1")->fetch()['count'];
$totalMunicipalities = $municipalities->rowCount();
$totalReports = $conn->query("SELECT COUNT(*) as count FROM tb_report WHERE rstatus = 'Pending'")->fetch()['count'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(rsales), 0) as total FROM tb_report WHERE rstatus = 'Validated'")->fetch()['total'] ?? 0;

$districtStats = $conn->query("
    SELECT rs.district, COUNT(r.id) as report_count, COALESCE(SUM(r.total_customer), 0) as total_customers, COALESCE(SUM(r.rsales), 0) as total_sales
    FROM tb_report r
    JOIN tb_resort rs ON r.resortid = rs.resortid
    WHERE r.rstatus = 'Validated'
    GROUP BY rs.district
")->fetchAll();
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Provincial Dashboard</h1>
            <p class="text-gray-500">Overview of all municipalities and resorts in Iloilo Province</p>
        </div>
        <a href="?page=provincial-add-municipality" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center gap-2">
            <i class="bi bi-plus"></i> Add Municipality
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-geo-alt text-blue-500 text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalMunicipalities; ?></h3>
            <p class="text-gray-500 text-sm">Municipalities</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-buildings text-emerald-500 text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalResorts; ?></h3>
            <p class="text-gray-500 text-sm">Total Resorts</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-clock-history text-amber-500 text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-amber-600"><?php echo $totalReports; ?></h3>
            <p class="text-gray-500 text-sm">Pending Reports</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-cash-stack text-emerald-500 text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-emerald-600">₱<?php echo number_format(($totalRevenue ?? 0) / 1000, 0); ?>K</h3>
            <p class="text-gray-500 text-sm">Total Revenue</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Municipalities</h2>
                    <p class="text-gray-500 text-sm">Manage municipality access</p>
                </div>
            </div>
            <div class="border-b border-gray-100">
                <div class="flex overflow-x-auto scrollbar-thin">
                    <button onclick="showDistrict('all')" id="tab-all" class="district-tab px-5 py-3 text-sm font-medium text-teal-600 border-b-2 border-teal-500 whitespace-nowrap">
                        All
                    </button>
                    <?php 
                    $districtNames = ['FIRST DISTRICT', 'SECOND DISTRICT', 'THIRD DISTRICT', 'FOURTH DISTRICT', 'FIFTH DISTRICT'];
                    foreach($districtNames as $i => $dist): 
                        $distKey = str_replace(' ', '-', strtolower($dist));
                    ?>
                    <button onclick="showDistrict('<?php echo $distKey; ?>')" id="tab-<?php echo $distKey; ?>" class="district-tab px-5 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap">
                        <?php 
                            $num = $i + 1;
                            $suffix = $num == 1 ? 'st' : ($num == 2 ? 'nd' : ($num == 3 ? 'rd' : 'th'));
                            echo $num . $suffix; ?> District
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Municipality</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">District</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Status</th>
                            <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="municipality-list" class="divide-y divide-gray-100">
                        <?php 
                        $ordSuffix = fn($n) => $n == 1 ? 'st' : ($n == 2 ? 'nd' : ($n == 3 ? 'rd' : 'th'));
                        $districtMap = ['FIRST DISTRICT' => 1, 'SECOND DISTRICT' => 2, 'THIRD DISTRICT' => 3, 'FOURTH DISTRICT' => 4, 'FIFTH DISTRICT' => 5];
                        $municipalities = $conn->query("SELECT * FROM tb_municipality ORDER BY district, mun");
                        while($mun = $municipalities->fetch()): 
                            $lastActivity = $mun['last_activity'] ?? null;
                            $status = 'Offline';
                            if($lastActivity && $lastActivity != '0000-00-00 00:00:00') {
                                $last = strtotime($lastActivity);
                                $now = time();
                                if(($now - $last) < 300) {
                                    $status = 'Active';
                                }
                            }
                            $districtKey = str_replace(' ', '-', strtolower($mun['district']));
                            $dNum = $districtMap[$mun['district']] ?? 0;
                        ?>
                        <tr class="hover:bg-gray-50 transition municipality-row" data-district="<?php echo $districtKey; ?>">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($mun['mun']); ?></p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600"><?php echo $dNum . $ordSuffix($dNum); ?> District</span>
                            </td>
                            <td class="px-5 py-4">
                                <?php if($status == 'Active'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    Active
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    Offline
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="?page=provincial-resorts&mun=<?php echo htmlspecialchars($mun['mun']); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
        function showDistrict(district) {
            document.querySelectorAll('.district-tab').forEach(tab => {
                tab.classList.remove('text-teal-600', 'border-b-2', 'border-teal-500');
                tab.classList.add('text-gray-500');
            });
            const activeTab = document.getElementById('tab-' + district);
            if (activeTab) {
                activeTab.classList.remove('text-gray-500');
                activeTab.classList.add('text-teal-600', 'border-b-2', 'border-teal-500');
            }
            
            document.querySelectorAll('.municipality-row').forEach(row => {
                if (district === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.district === district ? '' : 'none';
                }
            });
        }
        </script>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="?page=provincial-add-municipality" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-plus-circle text-teal-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Add Municipality</h4>
                        <p class="text-xs text-gray-500">Register new municipality</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=provincial-resorts" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-buildings text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Manage Resorts</h4>
                        <p class="text-xs text-gray-500">View all resorts</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=provincial-manage-reports" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-file-text text-emerald-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">View Reports</h4>
                        <p class="text-xs text-gray-500"><?php echo $totalReports; ?> pending</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=provincial-profile" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-person-gear text-amber-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Settings</h4>
                        <p class="text-xs text-gray-500">Account settings</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Revenue by District</h2>
            <p class="text-gray-500 text-sm">Sales performance across districts</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">District</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Reports</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total Guests</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($districtStats as $stat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($stat['district']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo $stat['report_count']; ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo number_format($stat['total_customers']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-emerald-600">₱<?php echo number_format($stat['total_sales'], 0); ?></p>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>