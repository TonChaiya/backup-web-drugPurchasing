<?php
require '../config.php';
require '../vendor/autoload.php'; // ใช้สำหรับการสร้าง PDF และ Word

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$format = $_GET['format'] ?? 'pdf'; // รับค่าจาก URL หรือใช้ค่าเริ่มต้นเป็น 'pdf'
$status = $_GET['status'];

// ดึงข้อมูลจากตาราง processed ที่มีสถานะ purchase_status = :status
$query = "SELECT * FROM processed WHERE purchase_status = :status";
$stmt = $con->prepare($query);
$stmt->execute([':status' => $status]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format == 'pdf') {
    if ($status == 'GPO') {
        include '../Purchasing/report_generators/generate_pdf.php';
    } elseif ($status == 'จัดซื้อบริษัท') {
        include '../Purchasing/report_generators/generate_purchase_pdf.php';
    } elseif ($status == 'บันทึกข้อความ') {
        include '../Purchasing/report_generators/generate_notepdf.php';
    }
} elseif ($format == 'word') {
    if ($status == 'GPO') {
        include '../Purchasing/report_generators/generate_word.php';
    } elseif ($status == 'จัดซื้อบริษัท') {
        include '../Purchasing/report_generators/generate_purchase_word.php';
    } elseif ($status == 'บันทึกข้อความ') {
        include '../Purchasing/report_generators/generate_noteword.php';
    }
} else {
    echo "รูปแบบไฟล์ไม่ถูกต้อง";
}
?>