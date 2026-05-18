<?php
session_start();
require_once '../connection.php';

if(isset($_GET['id'])) {
    $reportId = $_GET['id'];
    
    $stmt = $conn->prepare("
        SELECT r.*, rs.resortname, rs.resortaddress, rs.mun, rs.district, rs.contact_no
        FROM tb_report r 
        JOIN tb_resort rs ON r.resortid = rs.resortid 
        WHERE r.id = ?
    ");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();
    
    if($report) {
        $remarks = null;
        if($report['rstatus'] == 'Invalid') {
            $invStmt = $conn->prepare("SELECT remarks, idate FROM tb_invalid WHERE reportid = ? OR reportid = ? ORDER BY idate DESC LIMIT 1");
            $invStmt->execute([$reportId, $report['reportid']]);
            $remarks = $invStmt->fetch();
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Report Details - <?php echo $report['resortname']; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .report-box { border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .label { font-weight: bold; width: 200px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Province of Iloilo</h2>
                    <h3>Resort Monthly Report</h3>
                    <p><?php echo date('F Y', strtotime($report['rdate'])); ?></p>
                </div>
                
                <div class="report-box">
                    <h4>Resort Information</h4>
                    <p><span class="label">Resort Name:</span> <?php echo htmlspecialchars($report['resortname']); ?></p>
                    <p><span class="label">Address:</span> <?php echo htmlspecialchars($report['resortaddress']); ?></p>
                    <p><span class="label">Municipality:</span> <?php echo htmlspecialchars($report['mun']); ?></p>
                    <p><span class="label">District:</span> <?php echo htmlspecialchars($report['district']); ?></p>
                    <p><span class="label">Contact:</span> <?php echo htmlspecialchars($report['contact_no']); ?></p>
                </div>
                
                <div class="report-box">
                    <h4>Visitor Statistics</h4>
                    <p><span class="label">Domestic Visitors:</span> <?php echo number_format($report['dcx_quan']); ?></p>
                    <p><span class="label">- Male:</span> <?php echo number_format($report['male_domestic']); ?></p>
                    <p><span class="label">- Female:</span> <?php echo number_format($report['female_domestic']); ?></p>
                    <p><span class="label">Foreign Visitors:</span> <?php echo number_format($report['fcx_quan']); ?></p>
                    <p><span class="label">- Male:</span> <?php echo number_format($report['male_foreign']); ?></p>
                    <p><span class="label">- Female:</span> <?php echo number_format($report['female_foreign']); ?></p>
                    <p><span class="label">Total Visitors:</span> <?php echo number_format($report['total_customer']); ?></p>
                </div>
                
                <div class="report-box">
                    <h4>Financial Report</h4>
                    <p><span class="label">Total Sales:</span> ₱<?php echo number_format($report['rsales'], 2); ?></p>
                    <p><span class="label">Total Expenses:</span> ₱<?php echo number_format($report['rexpenses'], 2); ?></p>
                    <p><span class="label">Net Profit:</span> ₱<?php echo number_format($report['rsales'] - $report['rexpenses'], 2); ?></p>
                </div>
                
                <div class="report-box">
                    <h4>Report Status</h4>
                    <?php if($report['rstatus'] == 'Pending'): ?>
                        <p><span class="label">Status:</span> <span class="badge bg-warning text-dark">Pending</span></p>
                        <p class="text-muted"><small>This report is awaiting municipal validation.</small></p>
                    <?php elseif($report['rstatus'] == 'Validated'): ?>
                        <p><span class="label">Status:</span> <span class="badge bg-success">Validated</span></p>
                        <?php if($report['date_validated'] && $report['date_validated'] != '0000-00-00 00:00:00'): ?>
                        <p><span class="label">Date Validated:</span> <?php echo date('M j, Y h:i A', strtotime($report['date_validated'])); ?></p>
                        <?php endif; ?>
                    <?php elseif($report['rstatus'] == 'Invalid'): ?>
                        <p><span class="label">Status:</span> <span class="badge bg-danger">Invalid</span></p>
                        <?php if($remarks): ?>
                        <p><span class="label">Rejection Remarks:</span></p>
                        <div class="alert alert-danger mt-1"><?php echo htmlspecialchars($remarks['remarks']); ?></div>
                        <p><span class="label">Date Rejected:</span> <?php echo date('M j, Y h:i A', strtotime($remarks['idate'])); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-4">
                    <button onclick="window.print()" class="btn btn-primary">Print Report</button>
                    <button onclick="window.close()" class="btn btn-secondary">Close</button>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
?>