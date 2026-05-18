<?php
session_start();
require_once '../connection.php';

$status = $_GET['status'] ?? 'all';
$district = $_GET['district'] ?? 'all';

$sql = "
    SELECT r.rdate, rs.resortname, rs.mun, rs.district, 
           r.dcx_quan as domestic_visitors, r.fcx_quan as foreign_visitors, 
           r.total_customer, r.rsales as sales, r.rexpenses as expenses,
           (r.rsales - r.rexpenses) as profit, r.rstatus
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

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reports_export_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, [
    'Report Date', 'Resort Name', 'Municipality', 'District',
    'Domestic Visitors', 'Foreign Visitors', 'Total Customers',
    'Sales (₱)', 'Expenses (₱)', 'Profit (₱)', 'Status'
]);

// Add data rows
foreach($reports as $report) {
    fputcsv($output, [
        date('F Y', strtotime($report['rdate'])),
        $report['resortname'],
        $report['mun'],
        $report['district'],
        $report['domestic_visitors'],
        $report['foreign_visitors'],
        $report['total_customer'],
        number_format($report['sales'], 2),
        number_format($report['expenses'], 2),
        number_format($report['profit'], 2),
        $report['rstatus']
    ]);
}

fclose($output);
exit;
?>