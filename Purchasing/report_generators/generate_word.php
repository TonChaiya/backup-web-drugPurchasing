<?php
require dirname(__DIR__) . '../../config.php';
require dirname(__DIR__) . '../../vendor/autoload.php'; // ใช้สำหรับการสร้าง PDF

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// สร้างเอกสาร Word ใหม่
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// เพิ่มโลโก้
$imagePath = 'C:/laragon/www/logo.png'; // เส้นทางของรูปภาพ
if (file_exists($imagePath)) {
    $section->addImage($imagePath, [
        'width' => 50,
        'height' => 50,
        'alignment' => 'left',
    ]);
} else {
    $section->addText('ไม่พบโลโก้', ['color' => 'FF0000']);
}

// เพิ่มข้อความหัวเรื่อง
$section->addText('บันทึกข้อความ', ['bold' => true, 'size' => 21], ['alignment' => 'center']);

// เพิ่มเนื้อหาหลัก
$section->addText(
    'ส่วนราชการ กองสาธารณสุข (ฝ่ายบริหารงานสาธารณสุข) โทร. 053-596516 ต่อ 601 - 602',
    ['size' => 16]
);
$section->addText('ที่ ลพ.51010.05/');
$section->addText('เรื่อง');
$section->addText('เรียน นายกองค์การบริหารส่วนจังหวัดลำพูน');

// เพิ่มข้อความย่อหน้าแรก
$section->addText(
    "1.คำสั่งองค์การบริหารส่วนจังหวัดลำพูนที่.........../..........ลงวันที่ ..................................เรื่องแต่งตั้ง คณะกรรมการกำหนดรายละเอียดคุณลักษณะและราคากลางเพื่อจัดซื้อเวชภัณฑ์ยาสนับสนุนการให้บริการสาธารณะระดับปฐมภูมิของโรงพยาบาลส่งเสริมสุขภาพตำบลในสังกัดองค์การบริหารส่วนจังหวัดลำพูนประจำ เดือนตุลาคม 2567 - เดือนธันวาคม 2567 ประกอบด้วย",
    ['size' => 16]
);

// เพิ่มตาราง
$table = $section->addTable();
$table->addRow();
$table->addCell(1000)->addText('1.');
$table->addCell(4000)->addText('นางสมจิต ตันแปง');
$table->addCell(6000)->addText('พยาบาลวิชาชีพชำนาญการ');
$table->addCell(2000)->addText('ประธานกรรมการ');

$table->addRow();
$table->addCell(1000)->addText('2.');
$table->addCell(4000)->addText('นายนรงค์ชัย ใจยา');
$table->addCell(6000)->addText('เจ้าพนักงานเภสัชกรรมชำนาญงาน');
$table->addCell(2000)->addText('กรรมการ');

// เพิ่มข้อความย่อหน้าสุดท้าย
$section->addText(
    "โดยให้คณะกรรมการกำหนดรายละเอียดคุณลักษณะและราคากลางมีอำนาจหน้าที่ดังนี้พิจารณา กลั่นกรองและจัดทำTOR: Term of Reference เพื่อกำหนดขอบเขตของงานหรือรายละเอียดคุณลักษณะเฉพาะของพัสดุที่จะซื้อหรือจ้างให้เป็นไปตามวัตถุประสงค์ที่กำหนดไว้และกำหนดราคากลาง(ราคาอ้างอิง)ของงานที่จะ จัดซื้อจัดจ้างพร้อมทั้งรายงานผลต่อนายกองค์การบริหารส่วนจังหวัดลำพูน",
    ['size' => 16],
    ['alignment' => 'both']
);

// สร้างไฟล์ Word
$filename = 'note.docx';
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');
exit;
?>
