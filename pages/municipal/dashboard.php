<?php
$municipalId = $_SESSION['user_reg'];
$municipal = $conn->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$municipal->execute([$municipalId]);
$municipalData = $municipal->fetch();

$resorts = $conn->prepare("SELECT * FROM tb_resort WHERE mun = ? ORDER BY resortname");
$resorts->execute([$municipalData['mun']]);

$pendingReports = $conn->prepare("
    SELECT r.*, rs.resortname 
    FROM tb_report r 
    JOIN tb_resort rs ON r.resortid = rs.resortid 
    WHERE rs.mun = ? AND r.rstatus = 'Pending'
    ORDER BY r.rdate DESC
");
$pendingReports->execute([$municipalData['mun']]);

$totalResorts = $resorts->rowCount();

$statsQuery = $conn->prepare("
    SELECT COUNT(*) as count, COALESCE(SUM(total_customer), 0) as customers, COALESCE(SUM(rsales), 0) as sales 
    FROM tb_report r 
    JOIN tb_resort rs ON r.resortid = rs.resortid 
    WHERE rs.mun = ? AND r.rstatus = 'Validated'
");
$statsQuery->execute([$municipalData['mun']]);
$stats = $statsQuery->fetch();

$validatedReports = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM tb_report r 
    JOIN tb_resort rs ON r.resortid = rs.resortid 
    WHERE rs.mun = ? AND r.rstatus = 'Validated'
");
$validatedReports->execute([$municipalData['mun']]);
$validatedCount = $validatedReports->fetch()['count'];

$invalidReports = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM tb_report r 
    JOIN tb_resort rs ON r.resortid = rs.resortid 
    WHERE rs.mun = ? AND r.rstatus = 'Invalid'
");
$invalidReports->execute([$municipalData['mun']]);
$invalidCount = $invalidReports->fetch()['count'];
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                <i class="bi bi-building text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($municipalData['mun']); ?></h1>
                <p class="text-gray-500 flex items-center gap-1">
                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($municipalData['district']); ?> District
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="?page=municipal-manage-locations" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center gap-2">
                <i class="bi bi-geo"></i> Locations
            </a>
            <a href="?page=municipal-add-resort" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center gap-2">
                <i class="bi bi-plus"></i> Add Resort
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Resorts -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-buildings text-purple-500 text-xl"></i>
                </div>
                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Registry</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalResorts; ?></h3>
            <p class="text-gray-500 text-sm">Total Resorts</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-gray-400">
                <i class="bi bi-geo-alt"></i> In municipality
            </div>
        </div>

        <!-- Pending Reports -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-hourglass-split text-amber-500 text-xl"></i>
                </div>
                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-3xl font-bold text-amber-600"><?php echo $pendingReports->rowCount(); ?></h3>
            <p class="text-gray-500 text-sm">Pending Reports</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-amber-500">
                <i class="bi bi-exclamation-circle"></i> Awaiting validation
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-people text-blue-500 text-xl"></i>
                </div>
                <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Customers</span>
            </div>
            <h3 class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['customers'] ?? 0); ?></h3>
            <p class="text-gray-500 text-sm">Total Customers</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-blue-500">
                <i class="bi bi-check-circle"></i> All time
            </div>
        </div>

        <!-- Total Sales -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-cash-stack text-emerald-500 text-xl"></i>
                </div>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Revenue</span>
            </div>
            <h3 class="text-3xl font-bold text-emerald-600">₱<?php echo number_format(($stats['sales'] ?? 0) / 1000, 0); ?>K</h3>
            <p class="text-gray-500 text-sm">Total Sales</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-emerald-500">
                <i class="bi bi-graph-up"></i> Validated reports
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Resorts List -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Registered Resorts</h2>
                    <p class="text-gray-500 text-sm">Resorts in your municipality</p>
                </div>
                <a href="?page=municipal-add-resort" class="text-sm text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1">
                    <i class="bi bi-plus"></i> Add New
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Resort Name</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Location</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Entrance Fee</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Status</th>
                            <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if($totalResorts > 0): ?>
                            <?php while($resort = $resorts->fetch()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($resort['resortname']); ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($resort['barangay'] ?? $resort['resortaddress']); ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-800 font-medium">₱<?php echo number_format($resort['adultEntranceFee'], 0); ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <?php
                                    $lastActivity = $resort['last_activity'] ?? null;
                                    $onlineStatus = 'Offline';
                                    if($lastActivity && $lastActivity != '0000-00-00 00:00:00') {
                                        $last = strtotime($lastActivity);
                                        $now = time();
                                        if(($now - $last) < 300) {
                                            $onlineStatus = 'Online';
                                        }
                                    }
                                    ?>
                                    <?php if($onlineStatus == 'Online'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                        <i class="bi bi-circle-fill" style="font-size:6px"></i> Online
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        <i class="bi bi-circle-fill" style="font-size:6px"></i> Offline
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="?page=municipal-resort-reports&resort=<?php echo $resort['resortid']; ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                                        <i class="bi bi-file-text"></i> Reports
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="bi bi-building text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No resorts registered yet</p>
                                    <p class="text-gray-400 text-sm mb-3">Add your first resort to get started</p>
                                    <a href="?page=municipal-add-resort" class="inline-flex items-center gap-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                                        <i class="bi bi-plus"></i> Add Resort
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="?page=municipal-add-resort" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-plus-circle text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Add Resort</h4>
                        <p class="text-xs text-gray-500">Register new resort</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=municipal-manage-locations" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-geo text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Manage Locations</h4>
                        <p class="text-xs text-gray-500">Update resort locations</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=municipal-reports" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-file-text text-emerald-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">View Reports</h4>
                        <p class="text-xs text-gray-500"><?php echo $validatedCount; ?> validated</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=municipal-profile" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
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

            <!-- Report Stats Summary -->
            <div class="mt-6 pt-6 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-500 mb-3">REPORT STATUS</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 bg-amber-50 rounded-lg">
                        <span class="text-sm text-amber-700 flex items-center gap-2">
                            <i class="bi bi-hourglass-split"></i> Pending
                        </span>
                        <span class="font-semibold text-amber-700"><?php echo $pendingReports->rowCount(); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-emerald-50 rounded-lg">
                        <span class="text-sm text-emerald-700 flex items-center gap-2">
                            <i class="bi bi-check-circle"></i> Validated
                        </span>
                        <span class="font-semibold text-emerald-700"><?php echo $validatedCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
                        <span class="text-sm text-red-700 flex items-center gap-2">
                            <i class="bi bi-x-circle"></i> Invalid
                        </span>
                        <span class="font-semibold text-red-700"><?php echo $invalidCount; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Reports -->
    <?php if($pendingReports->rowCount() > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Pending Reports</h2>
                <p class="text-gray-500 text-sm">Reports awaiting validation</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Resort</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Customers</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Sales</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($report = $pendingReports->fetch()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($report['resortname']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo date('M j, Y', strtotime($report['rdate'])); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1 text-gray-600">
                                <i class="bi bi-people"></i> <?php echo $report['total_customer']; ?>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-800">₱<?php echo number_format($report['rsales'], 2); ?></p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="?page=municipal-reports&validate=<?php echo $report['id']; ?>" class="inline-flex items-center gap-1 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition">
                                <i class="bi bi-check-lg"></i> Validate
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>