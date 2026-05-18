<?php
$status = $_GET['status'] ?? 'all';
$district = $_GET['district'] ?? 'all';

$sql = "
    SELECT r.*, rs.resortname, rs.mun, rs.district, rs.resortaddress
    FROM tb_report r 
    JOIN tb_resort rs ON r.resortid = rs.resortid 
    WHERE 1=1
";
$params = [];

if($status != 'all') {
    $sql .= " AND r.rstatus = ?";
    $params[] = $status;
}
if($district != 'all') {
    $sql .= " AND rs.district = ?";
    $params[] = $district;
}
$sql .= " ORDER BY r.rdate DESC";

$reports = $conn->prepare($sql);
$reports->execute($params);

$districts = $conn->query("SELECT DISTINCT district FROM tb_resort ORDER BY district")->fetchAll();

$summary = $conn->query("
    SELECT 
        COUNT(r.id) as total_reports,
        COALESCE(SUM(r.total_customer), 0) as total_customers,
        COALESCE(SUM(r.rsales), 0) as total_sales,
        AVG(r.rsales - r.rexpenses) as avg_profit
    FROM tb_report r
    JOIN tb_resort rs ON r.resortid = rs.resortid
    WHERE r.rstatus = 'Validated'
")->fetch();
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Report Monitoring</h1>
            <p class="text-gray-500">View and analyze all resort reports across the province</p>
        </div>
        <button onclick="exportReports()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center gap-2">
            <i class="bi bi-download"></i> Export CSV
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm">Total Reports</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($summary['total_reports'] ?? 0); ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm">Total Customers</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($summary['total_customers'] ?? 0); ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm">Total Sales</p>
            <h3 class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($summary['total_sales'] ?? 0, 0); ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm">Average Profit</p>
            <h3 class="text-2xl font-bold text-purple-600">₱<?php echo number_format($summary['avg_profit'] ?? 0, 0); ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <select id="statusFilter" class="px-4 py-2 border border-gray-200 rounded-xl text-sm">
                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Validated" <?php echo $status == 'Validated' ? 'selected' : ''; ?>>Validated</option>
                <option value="Invalid" <?php echo $status == 'Invalid' ? 'selected' : ''; ?>>Invalid</option>
            </select>
            <select id="districtFilter" class="px-4 py-2 border border-gray-200 rounded-xl text-sm">
                <option value="all" <?php echo $district == 'all' ? 'selected' : ''; ?>>All Districts</option>
                <?php foreach($districts as $d): ?>
                <option value="<?php echo $d['district']; ?>" <?php echo $district == $d['district'] ? 'selected' : ''; ?>><?php echo $d['district']; ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="applyFilters()" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl text-sm font-medium">
                Apply Filters
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Resort</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Municipality</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Customers</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Sales</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Expenses</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Profit</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($report = $reports->fetch()): ?>
                    <?php $profit = $report['rsales'] - $report['rexpenses']; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($report['resortname']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo htmlspecialchars($report['mun']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-600"><?php echo date('M Y', strtotime($report['rdate'])); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-gray-800"><?php echo number_format($report['total_customer']); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-emerald-600 font-medium">₱<?php echo number_format($report['rsales'], 0); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-red-600">₱<?php echo number_format($report['rexpenses'], 0); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-medium <?php echo $profit >= 0 ? 'text-emerald-600' : 'text-red-600'; ?>">₱<?php echo number_format($profit, 0); ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <?php if($report['rstatus'] == 'Validated'): ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Validated</span>
                            <?php elseif($report['rstatus'] == 'Invalid'): ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Invalid</span>
                            <?php else: ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const district = document.getElementById('districtFilter').value;
    window.location.href = `?page=provincial-manage-reports&status=${status}&district=${district}`;
}

function exportReports() {
    const status = document.getElementById('statusFilter').value;
    const district = document.getElementById('districtFilter').value;
    window.open(`api/export-reports.php?status=${status}&district=${district}`, '_blank');
}
</script>