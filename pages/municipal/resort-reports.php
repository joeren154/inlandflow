<?php
$municipalId = $_SESSION['user_reg'];
$municipal = $conn->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$municipal->execute([$municipalId]);
$municipalData = $municipal->fetch();

$resortFilter = $_GET['resort'] ?? '';
$status = $_GET['status'] ?? 'all';

$resortName = '';
if($resortFilter) {
    $rn = $conn->prepare("SELECT resortname FROM tb_resort WHERE resortid = ? AND mun = ?");
    $rn->execute([$resortFilter, $municipalData['mun']]);
    $resortName = $rn->fetch()['resortname'] ?? '';
}

$sql = "
    SELECT r.*, rs.resortname, rs.resortaddress, rs.contact_no
    FROM tb_report r 
    JOIN tb_resort rs ON r.resortid = rs.resortid 
    WHERE rs.mun = ?
";
$params = [$municipalData['mun']];

if($resortFilter) {
    $sql .= " AND r.resortid = ?";
    $params[] = $resortFilter;
}

if($status != 'all') {
    $sql .= " AND r.rstatus = ?";
    $params[] = $status;
}
$sql .= " ORDER BY r.rdate DESC";

$reports = $conn->prepare($sql);
$reports->execute($params);

// Handle validation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['report_id'])) {
    $reportId = (int)$_POST['report_id'];
    $action = $_POST['action'];
    
    // Verify report belongs to a resort in this municipality
    $verify = $conn->prepare("SELECT r.id FROM tb_report r JOIN tb_resort rs ON r.resortid = rs.resortid WHERE r.id = ? AND rs.mun = ?");
    $verify->execute([$reportId, $municipalData['mun']]);
    if($verify->rowCount() == 0) {
        $error = "Report not found or not in your municipality!";
    } else {
        if($action == 'validate') {
            $update = $conn->prepare("UPDATE tb_report SET rstatus = 'Validated', date_validated = NOW() WHERE id = ? AND rstatus = 'Pending'");
            $update->execute([$reportId]);
            $success = "Report validated successfully!";
        } elseif($action == 'reject') {
            $reason = $_POST['reason'] ?? '';
            $update = $conn->prepare("UPDATE tb_report SET rstatus = 'Invalid' WHERE id = ? AND rstatus = 'Pending'");
            $update->execute([$reportId]);
            
            // Add to invalid table
            $report = $conn->prepare("SELECT r.*, rs.resortname, rs.mun, rs.district FROM tb_report r JOIN tb_resort rs ON r.resortid = rs.resortid WHERE r.id = ?");
            $report->execute([$reportId]);
            $rep = $report->fetch();
            
            $insert = $conn->prepare("INSERT INTO tb_invalid (reportid, resortname, mun, district, remarks) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$reportId, $rep['resortname'], $rep['mun'], $rep['district'], $reason]);
            $success = "Report rejected!";
        }
    }
}
?>

<div class="p-6">
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <?php if($resortFilter && $resortName): ?>
                <a href="?page=dashboard" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-2">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($resortName); ?></h1>
                <p class="text-gray-500">Monthly reports for this resort</p>
                <?php else: ?>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Resort Reports</h1>
                <p class="text-gray-500">Manage and validate resort monthly reports</p>
                <?php endif; ?>
            </div>
            <div>
                <select id="statusFilter" class="px-4 py-2 border border-gray-200 rounded-xl">
                    <option value="all">All Reports</option>
                    <option value="Pending">Pending</option>
                    <option value="Validated">Validated</option>
                    <option value="Invalid">Invalid</option>
                </select>
            </div>
        </div>
    </div>
        
        <?php if(isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Reports Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Report ID</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Resort</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Report Date</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Domestic</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Foreign</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Sales</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Expenses</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Status</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while($report = $reports->fetch()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <span class="font-mono text-sm font-semibold text-purple-600">#<?php echo $report['id']; ?></span>
                            </td>
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($report['resortname']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($report['resortaddress']); ?></p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600"><?php echo date('M Y', strtotime($report['rdate'])); ?></td>
                            <td class="px-5 py-4 text-gray-600"><?php echo number_format($report['dcx_quan']); ?></td>
                            <td class="px-5 py-4 text-gray-600"><?php echo number_format($report['fcx_quan']); ?></td>
                            <td class="px-5 py-4 font-semibold text-gray-800"><?php echo number_format($report['total_customer']); ?></td>
                            <td class="px-5 py-4 text-emerald-600">₱<?php echo number_format($report['rsales'], 2); ?></td>
                            <td class="px-5 py-4 text-red-600">₱<?php echo number_format($report['rexpenses'], 2); ?></td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php 
                                    echo $report['rstatus'] == 'Validated' ? 'bg-emerald-100 text-emerald-700' : 
                                        ($report['rstatus'] == 'Invalid' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'); 
                                ?>">
                                    <?php echo $report['rstatus']; ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <?php if($report['rstatus'] == 'Pending'): ?>
                                <button onclick="validateReport(<?php echo $report['id']; ?>)" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-sm hover:bg-emerald-200 transition">
                                    <i class="bi bi-check-lg"></i> Validate
                                </button>
                                <button onclick="rejectReport(<?php echo $report['id']; ?>)" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                                <?php else: ?>
                                <button onclick="viewReport(<?php echo $report['id']; ?>)" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-100 text-purple-600 rounded-lg text-sm hover:bg-purple-200 transition">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function validateReport(reportId) {
    Swal.fire({
        title: 'Validate Report?',
        text: 'Are you sure you want to validate this report?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Validate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if(result.isConfirmed) {
            $.post('', {report_id: reportId, action: 'validate'}, function() {
                location.reload();
            });
        }
    });
}

function rejectReport(reportId) {
    Swal.fire({
        title: 'Reject Report',
        input: 'textarea',
        inputLabel: 'Reason for rejection',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if(result.isConfirmed && result.value) {
            $.post('', {report_id: reportId, action: 'reject', reason: result.value}, function() {
                location.reload();
            });
        }
    });
}

function viewReport(reportId) {
    window.open('api/view-report.php?id=' + reportId, '_blank');
}

document.getElementById('statusFilter').addEventListener('change', function() {
    window.location.href = '?page=municipal-resort-reports&status=' + this.value;
});
</script>