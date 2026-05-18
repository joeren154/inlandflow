<?php
$resortId = $_SESSION['user_reg'];

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<script>window.location.href = "index.php?page=login";</script>';
    exit;
}

// Handle report submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $rdate = $_POST['rdate'] . '-01';
    $maleDomestic = (int)($_POST['male_domestic'] ?? 0);
    $femaleDomestic = (int)($_POST['female_domestic'] ?? 0);
    $maleForeign = (int)($_POST['male_foreign'] ?? 0);
    $femaleForeign = (int)($_POST['female_foreign'] ?? 0);
    $rsales = (float)($_POST['rsales'] ?? 0);
    $rexpenses = (float)($_POST['rexpenses'] ?? 0);
    
    $dcxQuan = $maleDomestic + $femaleDomestic;
    $fcxQuan = $maleForeign + $femaleForeign;
    $totalCustomer = $dcxQuan + $fcxQuan;
    
    $check = $conn->prepare("SELECT id FROM tb_report WHERE resortid = ? AND rdate = ?");
    $check->execute([$resortId, $rdate]);
    if($check->rowCount() > 0) {
        $submitError = "A report for this month already exists!";
    } else {
        $nextId = $conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM tb_report")->fetch()['next_id'];
        $nextReportId = $conn->query("SELECT COALESCE(MAX(reportid), 1000) + 1 AS next_id FROM tb_report")->fetch()['next_id'];
        $insert = $conn->prepare("INSERT INTO tb_report (id, reportid, resortid, male_domestic, female_domestic, dcx_quan, male_foreign, female_foreign, fcx_quan, total_customer, rdate, rsales, rexpenses, rstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $insert->execute([$nextId, $nextReportId, $resortId, $maleDomestic, $femaleDomestic, $dcxQuan, $maleForeign, $femaleForeign, $fcxQuan, $totalCustomer, $rdate, $rsales, $rexpenses]);
        header('Location: index.php?page=resort-reports&success=1');
        exit;
    }
}

// Handle report update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_report'])) {
    $id = (int)$_POST['id'];
    $rdate = $_POST['rdate'] . '-01';
    $maleDomestic = (int)($_POST['male_domestic'] ?? 0);
    $femaleDomestic = (int)($_POST['female_domestic'] ?? 0);
    $maleForeign = (int)($_POST['male_foreign'] ?? 0);
    $femaleForeign = (int)($_POST['female_foreign'] ?? 0);
    $rsales = (float)($_POST['rsales'] ?? 0);
    $rexpenses = (float)($_POST['rexpenses'] ?? 0);
    
    $dcxQuan = $maleDomestic + $femaleDomestic;
    $fcxQuan = $maleForeign + $femaleForeign;
    $totalCustomer = $dcxQuan + $fcxQuan;
    
    $update = $conn->prepare("UPDATE tb_report SET male_domestic = ?, female_domestic = ?, dcx_quan = ?, male_foreign = ?, female_foreign = ?, fcx_quan = ?, total_customer = ?, rdate = ?, rsales = ?, rexpenses = ? WHERE id = ? AND resortid = ? AND rstatus = 'Pending'");
    $update->execute([$maleDomestic, $femaleDomestic, $dcxQuan, $maleForeign, $femaleForeign, $fcxQuan, $totalCustomer, $rdate, $rsales, $rexpenses, $id, $resortId]);
    header('Location: index.php?page=resort-reports&success=1');
    exit;
}

// Handle report deletion
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_report'])) {
    $id = (int)$_POST['id'];
    $delete = $conn->prepare("DELETE FROM tb_report WHERE id = ? AND resortid = ? AND rstatus = 'Pending'");
    $delete->execute([$id, $resortId]);
    header('Location: index.php?page=resort-reports&success=1');
    exit;
}

$activeTab = $_GET['tab'] ?? 'All';
$validTabs = ['All', 'Pending', 'Validated', 'Invalid'];
if(!in_array($activeTab, $validTabs)) $activeTab = 'All';

// Get reports for current tab
if($activeTab === 'All') {
    $reports = $conn->prepare("SELECT * FROM tb_report WHERE resortid = ? ORDER BY rdate DESC");
    $reports->execute([$resortId]);
} else {
    $reports = $conn->prepare("SELECT * FROM tb_report WHERE resortid = ? AND rstatus = ? ORDER BY rdate DESC");
    $reports->execute([$resortId, $activeTab]);
}

// Get badge counts
$badgeCounts = [];
foreach($validTabs as $tab) {
    if($tab === 'All') {
        $cnt = $conn->prepare("SELECT COUNT(*) FROM tb_report WHERE resortid = ?");
        $cnt->execute([$resortId]);
    } else {
        $cnt = $conn->prepare("SELECT COUNT(*) FROM tb_report WHERE resortid = ? AND rstatus = ?");
        $cnt->execute([$resortId, $tab]);
    }
    $badgeCounts[$tab] = $cnt->fetchColumn();
}

// Get report statistics
$stats = $conn->prepare("SELECT SUM(total_customer) as total_customers, SUM(rsales) as total_sales, SUM(rexpenses) as total_expenses FROM tb_report WHERE resortid = ? AND rstatus = 'Validated'");
$stats->execute([$resortId]);
$reportStats = $stats->fetch();

// Get invalid report remarks
$invalidReports = [];
if($activeTab === 'Invalid') {
    $inv = $conn->prepare("SELECT i.*, r.rdate FROM tb_invalid i JOIN tb_report r ON i.reportid = r.id WHERE r.resortid = ? ORDER BY i.idate DESC");
    $inv->execute([$resortId]);
    while($row = $inv->fetch()) {
        $invalidReports[$row['reportid']] = $row;
    }
}
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Resort Reports</h1>
            <p class="text-gray-500">Submit and manage monthly reports</p>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
        <span class="text-emerald-700">Report action completed successfully!</span>
    </div>
    <?php endif; ?>

    <?php if(isset($submitError)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-exclamation-circle text-red-600 text-xl"></i>
        <span class="text-red-700"><?php echo htmlspecialchars($submitError); ?></span>
    </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Validated Visitors</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($reportStats['total_customers'] ?? 0); ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Validated Sales</p>
            <h3 class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($reportStats['total_sales'] ?? 0, 2); ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Validated Expenses</p>
            <h3 class="text-2xl font-bold text-red-600">₱<?php echo number_format($reportStats['total_expenses'] ?? 0, 2); ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Submit Report Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
                <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="bi bi-file-earmark-plus text-purple-500"></i> Submit Monthly Report</h2>
                <form method="POST">
                    <input type="hidden" name="submit_report" value="1">
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Month *</label>
                        <input type="month" name="rdate" class="w-full px-4 py-2 border border-gray-200 rounded-xl" required>
                    </div>
                    
                    <h3 class="text-sm font-semibold text-gray-600 mb-2 mt-4">Domestic Visitors</h3>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Male</label>
                            <input type="number" name="male_domestic" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" value="0">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Female</label>
                            <input type="number" name="female_domestic" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" value="0">
                        </div>
                    </div>
                    
                    <h3 class="text-sm font-semibold text-gray-600 mb-2 mt-4">Foreign Visitors</h3>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Male</label>
                            <input type="number" name="male_foreign" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" value="0">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Female</label>
                            <input type="number" name="female_foreign" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" value="0">
                        </div>
                    </div>
                    
                    <h3 class="text-sm font-semibold text-gray-600 mb-2 mt-4">Financial</h3>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sales (₱)</label>
                            <input type="number" name="rsales" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" step="0.01" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Expenses (₱)</label>
                            <input type="number" name="rexpenses" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">
                        <i class="bi bi-send"></i> Submit Report
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Report History -->
        <div class="lg:col-span-3">
            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-4 overflow-x-auto">
                <div class="flex min-w-max">
                    <?php foreach($validTabs as $tab): 
                        $isActive = $tab === $activeTab;
                        $badge = $badgeCounts[$tab] ?? 0;
                    ?>
                    <a href="?page=resort-reports&tab=<?php echo $tab; ?>" 
                       class="px-5 py-3 text-sm font-medium border-b-2 transition flex items-center gap-2 whitespace-nowrap
                              <?php echo $isActive ? 'border-purple-500 text-purple-600 bg-purple-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $tab; ?>
                        <?php if($badge > 0): ?>
                        <span class="px-2 py-0.5 bg-<?php echo $isActive ? 'purple' : 'gray'; ?>-100 text-<?php echo $isActive ? 'purple' : 'gray'; ?>-600 text-xs rounded-full"><?php echo $badge; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">#</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Report Date</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Domestic</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Foreign</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Total</th>
                                <th class="text-right text-xs font-semibold text-gray-500 uppercase px-4 py-3">Sales</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            $rowNum = 0;
                            while($report = $reports->fetch()): 
                                $rowNum++;
                                $statusClass = match($report['rstatus']) {
                                    'Validated' => 'bg-emerald-100 text-emerald-700',
                                    'Invalid' => 'bg-red-100 text-red-700',
                                    default => 'bg-amber-100 text-amber-700'
                                };
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-600"><?php echo $rowNum; ?></td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800"><?php echo date('F Y', strtotime($report['rdate'])); ?></p>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600"><?php echo number_format($report['dcx_quan']); ?></td>
                                <td class="px-4 py-3 text-center text-gray-600"><?php echo number_format($report['fcx_quan']); ?></td>
                                <td class="px-4 py-3 text-center font-medium text-gray-800"><?php echo number_format($report['total_customer']); ?></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-800">₱<?php echo number_format($report['rsales'], 2); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                        <?php echo $report['rstatus']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="api/view-report.php?id=<?php echo $report['id']; ?>" target="_blank" class="text-blue-600 hover:text-blue-700" title="View Report">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if($report['rstatus'] === 'Pending'): ?>
                                        <button onclick="openEditModal(<?php echo $report['id']; ?>, '<?php echo date('Y-m', strtotime($report['rdate'])); ?>', <?php echo $report['male_domestic']; ?>, <?php echo $report['female_domestic']; ?>, <?php echo $report['male_foreign']; ?>, <?php echo $report['female_foreign']; ?>, <?php echo $report['rsales']; ?>, <?php echo $report['rexpenses']; ?>)" class="text-purple-600 hover:text-purple-700" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="deleteReport(<?php echo $report['id']; ?>)" class="text-red-600 hover:text-red-700" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php elseif($report['rstatus'] === 'Invalid'): ?>
                                            <?php 
                                            $inv = $conn->prepare("SELECT * FROM tb_invalid WHERE reportid = ?");
                                            $inv->execute([$report['id']]);
                                            $invalidData = $inv->fetch();
                                            ?>
                                            <?php if($invalidData): ?>
                                            <button onclick="showRemarks('<?php echo htmlspecialchars($invalidData['remarks']); ?>', '<?php echo date('M j, Y', strtotime($invalidData['idate'])); ?>')" class="text-red-600 hover:text-red-700" title="View Remarks">
                                                <i class="bi bi-exclamation-circle"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if($rowNum === 0): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="bi bi-clipboard-x text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No reports found</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Report Modal -->
<div id="editReportModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Edit Report</h3>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="update_report" value="1">
            <input type="hidden" name="id" id="editId">
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Report Month *</label>
                <input type="month" name="rdate" id="editRdate" class="w-full px-4 py-2 border border-gray-200 rounded-xl" required>
            </div>
            
            <h3 class="text-sm font-semibold text-gray-600 mb-2 mt-3">Domestic Visitors</h3>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Male</label>
                    <input type="number" name="male_domestic" id="editMaleDom" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Female</label>
                    <input type="number" name="female_domestic" id="editFemaleDom" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0">
                </div>
            </div>
            
            <h3 class="text-sm font-semibold text-gray-600 mb-2 mt-3">Foreign Visitors</h3>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Male</label>
                    <input type="number" name="male_foreign" id="editMaleFor" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Female</label>
                    <input type="number" name="female_foreign" id="editFemaleFor" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0">
                </div>
            </div>
            
            <h3 class="text-sm font-semibold text-gray-600 mb-2 mt-3">Financial</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sales (₱)</label>
                    <input type="number" name="rsales" id="editSales" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Expenses (₱)</label>
                    <input type="number" name="rexpenses" id="editExpenses" class="w-full px-4 py-2 border border-gray-200 rounded-xl" min="0" step="0.01">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Remarks Modal -->
<div id="remarksModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Invalid Report Remarks</h3>
        </div>
        <div class="p-6">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Invalidated</label>
                <p class="text-gray-800" id="remarksDate"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <div class="p-4 bg-red-50 rounded-xl text-red-700 text-sm" id="remarksText"></div>
            </div>
            <button onclick="closeRemarksModal()" class="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Close</button>
        </div>
    </div>
</div>

<script>
function openEditModal(id, rdate, maleDom, femaleDom, maleFor, femaleFor, sales, expenses) {
    document.getElementById('editId').value = id;
    document.getElementById('editRdate').value = rdate;
    document.getElementById('editMaleDom').value = maleDom;
    document.getElementById('editFemaleDom').value = femaleDom;
    document.getElementById('editMaleFor').value = maleFor;
    document.getElementById('editFemaleFor').value = femaleFor;
    document.getElementById('editSales').value = sales;
    document.getElementById('editExpenses').value = expenses;
    document.getElementById('editReportModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editReportModal').classList.add('hidden');
}

function deleteReport(id) {
    if(!confirm('Delete this pending report?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="delete_report" value="1"><input type="hidden" name="id" value="' + id + '">';
    document.body.appendChild(form);
    form.submit();
}

function showRemarks(remarks, date) {
    document.getElementById('remarksDate').textContent = date;
    document.getElementById('remarksText').textContent = remarks;
    document.getElementById('remarksModal').classList.remove('hidden');
}

function closeRemarksModal() {
    document.getElementById('remarksModal').classList.add('hidden');
}

document.getElementById('editReportModal').addEventListener('click', function(e) {
    if(e.target === this) closeEditModal();
});
document.getElementById('remarksModal').addEventListener('click', function(e) {
    if(e.target === this) closeRemarksModal();
});
</script>

