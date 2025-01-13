<?php
require dirname(__DIR__) . '../../config.php';
require dirname(__DIR__) . '../../vendor/autoload.php'; // โหลด PHPWord

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$status = $_GET['status'];

// ดึงข้อมูลจากตาราง processed ที่มีสถานะ purchase_status = :status
$query = "SELECT * FROM processed WHERE purchase_status = :status ORDER BY working_code";
$stmt = $con->prepare($query);
$stmt->execute([':status' => $status]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สร้างอ็อบเจกต์ PHPWord
$phpWord = new PhpWord();

// เพิ่มฟอนต์ TH Sarabun IT
$fontPath = dirname(__DIR__) . '../../vendor/tecnickcom/tcpdf/tools/THSarabunIT.ttf'; // ระบุ path ไปยังไฟล์ฟอนต์
$phpWord->addFontStyle('thaiFont', [
    'name' => 'TH Sarabun IT',
    'size' => 16, // ขนาดฟอนต์ 16pt
    'color' => '000000',
]);
$phpWord->addTitleStyle(1, [
    'name' => 'TH Sarabun IT',
    'size' => 20, // ขนาดฟอนต์ 20pt สำหรับหัวข้อ
    'bold' => true,
    'color' => '000000',
]);

// เพิ่ม Section ในเอกสาร
$section = $phpWord->addSection();

// เพิ่มหัวข้อเอกสาร
$section->addText('รายงานสถานะ: ' . $status, 'thaiFont');
$section->addTextBreak(2); // เพิ่มบรรทัดว่าง 2 บรรทัด

// เริ่มสร้างเนื้อหา
$index = 1;  // เริ่มนับลำดับที่
$item_count = 0; // ตัวนับรายการต่อหน้า

foreach ($data as $row) {
    if ($item_count == 3) { // เปลี่ยนหน้าใหม่หลังจากแสดง 3 รายการ
        $section->addPageBreak();
        $item_count = 0;
    }

    $item_code = htmlspecialchars($row['item_code']);
    $total_quantity = number_format($row['total_quantity']);
    $format_item_code = htmlspecialchars($row['format_item_code']);
    $packing_size = htmlspecialchars($row['packing_size']);

    // ดึงชื่อยา จากตาราง medicine_info โดยใช้ working_code
    $medicine_name_query = "SELECT itemcode FROM medicine_info WHERE working_code = :working_code";
    $medicine_name_stmt = $con->prepare($medicine_name_query);
    $medicine_name_stmt->execute([':working_code' => $row['working_code']]);
    $medicine_name_info = $medicine_name_stmt->fetch(PDO::FETCH_ASSOC);

    // แสดงข้อมูล itemcode แทน item_code
    $medicine_name = htmlspecialchars($medicine_name_info['itemcode']);

    // สร้างตารางสำหรับจัดรูปแบบข้อมูล (ไม่แสดงเส้นตาราง)
    $table = $section->addTable([
        'borderSize' => 0, // ไม่แสดงเส้นตาราง
        'borderColor' => 'FFFFFF', // สีขาว (ไม่แสดงเส้นตาราง)
        'cellMargin' => 50,
    ]);

    // แถวที่ 1: ลำดับที่ + ชื่อยา (ซ้าย) และจำนวน + หน่วย (ขวา)
    $table->addRow();
    $table->addCell(2000)->addText('ลำดับที่ ' . $index++, 'thaiFont'); // คอลัมน์ลำดับที่
    $table->addCell(5000)->addText('ชื่อยา: ' . $medicine_name, 'thaiFont'); // คอลัมน์ชื่อยา
    $table->addCell(3000)->addText('จำนวน: ' . $total_quantity . ' ' . $format_item_code, 'thaiFont'); // คอลัมน์จำนวน

    // แถวที่ 2: ขนาดบรรจุ
    $section->addText('ขนาดบรรจุ: ' . $packing_size, 'thaiFont');

    // แถวที่ 3: รูปแบบ
    $medicine_query = "SELECT format_itemcode FROM medicine_info WHERE working_code = :working_code";
    $medicine_stmt = $con->prepare($medicine_query);
    $medicine_stmt->execute([':working_code' => $row['working_code']]);
    $medicine_info = $medicine_stmt->fetch(PDO::FETCH_ASSOC);
    $section->addText('รูปแบบ: ' . htmlspecialchars($medicine_info['format_itemcode']), 'thaiFont');

    // แถวที่ 4: ประกอบด้วยตัวยา
    $section->addText('ประกอบด้วยตัวยา: ' . $item_code, 'thaiFont');

    // แถวที่ 5: ภาชนะบรรจุ
    $container_query = "SELECT container1 FROM medicine_info WHERE working_code = :working_code";
    $container_stmt = $con->prepare($container_query);
    $container_stmt->execute([':working_code' => $row['working_code']]);
    $container_info = $container_stmt->fetch(PDO::FETCH_ASSOC);

    // แสดงข้อมูล container1
    $container_text = htmlspecialchars($container_info['container1']);
    $section->addText('ภาชนะบรรจุ: ' . $container_text, 'thaiFont');

    // เพิ่มบรรทัดว่างเพื่อคั่นรายการ
    $section->addTextBreak(2);

    $item_count++;
}

// บันทึกไฟล์ชั่วคราว
$tempFilePath = sys_get_temp_dir() . '/report-' . time() . '.docx';
$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save($tempFilePath);

// ตรวจสอบว่าไฟล์ถูกสร้างหรือไม่
if (!file_exists($tempFilePath)) {
    die('ไม่สามารถสร้างไฟล์ Word ได้');
}

// ตั้งชื่อไฟล์ตามสถานะและจำนวนรายการ
$fileName = 'รายงานสถานะ_' . $status . '_จำนวน_' . count($data) . '_รายการ.docx';

// กำหนด HTTP Headers สำหรับการดาวน์โหลดไฟล์
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tempFilePath));

// ส่งไฟล์ไปยังเบราว์เซอร์
readfile($tempFilePath);

// ลบไฟล์ชั่วคราวหลังดาวน์โหลด
unlink($tempFilePath);
exit;
?>