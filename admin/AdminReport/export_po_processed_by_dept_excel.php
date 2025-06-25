<?php
// ดาวน์โหลด excel แยกชีทตามหน่วยเบิก po_processed
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
    $sql = "SELECT * FROM po_processed $whereSql ORDER BY dept_id, date DESC, po_number DESC";
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()));
}

// แยกข้อมูลตาม dept_id
$byDept = [];
foreach ($rows as $row) {
    $byDept[$row['dept_id']][] = $row;
}

$spreadsheet = new Spreadsheet();
$headers = ['#','เลขที่ใบเบิก','วันที่','หน่วยเบิก','working_code','item_code','format_item_code','จำนวน','ราคา','หมายเหตุ','packing_size','total_value','status','purchase_status'];

$first = true;
foreach ($byDept as $dept => $deptRows) {
    $sheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
    $sheet->setTitle(mb_substr($dept,0,31));
    $sheet->fromArray($headers, NULL, 'A1');
    $rowNum = 2;
    foreach ($deptRows as $i => $row) {
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
    $first = false;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="po_processed_by_dept.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
