<?php
// ดาวน์โหลด excel รวมข้อมูล po_processed
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$purchase_status = isset($_GET['purchase_status']) ? $_GET['purchase_status'] : 'all';

$where = [];
$params = [];
if ($status !== 'all') {
    $where[] = 'status = :status';
    $params[':status'] = $status;
}
if ($purchase_status !== 'all') {
    $where[] = 'purchase_status = :purchase_status';
    $params[':purchase_status'] = $purchase_status;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

try {
    $sql = "SELECT * FROM po_processed $whereSql ORDER BY date DESC, po_number DESC";
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()));
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('po_processed');

$headers = ['#','เลขที่ใบเบิก','วันที่','หน่วยเบิก','working_code','item_code','format_item_code','จำนวน','ราคา','หมายเหตุ','packing_size','total_value','status','purchase_status'];
$sheet->fromArray($headers, NULL, 'A1');

$rowNum = 2;
foreach ($rows as $i => $row) {
    $sheet->fromArray([
        $i+1,
        $row['po_number'],
        $row['date'],
        $row['dept_id'],
        $row['working_code'],
        $row['item_code'],
        $row['format_item_code'],
        $row['quantity'],
        $row['price'],
        $row['remarks'],
        $row['packing_size'],
        $row['total_value'],
        $row['status'],
        $row['purchase_status']
    ], NULL, 'A'.$rowNum);
    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="po_processed_report.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
