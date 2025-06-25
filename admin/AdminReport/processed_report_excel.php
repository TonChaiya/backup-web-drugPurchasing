<?php
// processed_report_excel.php
// Export รายงาน processed เป็น Excel

session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusList = array_filter(array_map('trim', explode(',', $status)));

$params = [];
$sql = "SELECT * FROM processed";
if (!in_array('all', $statusList) && count($statusList) > 0) {
    $inClause = implode(',', array_fill(0, count($statusList), '?'));
    $sql .= " WHERE status IN ($inClause)";
    $params = $statusList;
}
$sql .= " ORDER BY processed_at DESC, id DESC";

$stmt = $con->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$headers = ['ID', 'Working Code', 'Item Code', 'Format Item Code', 'Total Qty', 'Price', 'Packing Size', 'Total Value', 'Status', 'Purchase Status', 'Processed At', 'หมายเหตุ'];
$sheet->fromArray($headers, NULL, 'A1');

// Data
$rowNum = 2;
foreach ($rows as $row) {
    $sheet->fromArray([
        $row['id'],
        $row['working_code'],
        $row['item_code'],
        $row['format_item_code'],
        $row['total_quantity'],
        $row['price'],
        $row['packing_size'],
        $row['total_value'],
        $row['status'],
        $row['purchase_status'],
        $row['processed_at'],
        $row['remarks'],
    ], NULL, 'A' . $rowNum);
    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="processed_report.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
