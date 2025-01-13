<?php
require 'vendor/autoload.php'; // โหลด PhpWord

// สร้างไฟล์ Word ด้วย PhpOffice\PhpWord\PhpWord
$phpWord = new \PhpOffice\PhpWord\PhpWord();

// ตั้งค่าฟอนต์และรูปแบบ
$fontStyle = ['name' => 'TH Sarabun IT', 'size' => 14];
$titleStyle = ['name' => 'TH Sarabun IT', 'size' => 16, 'bold' => true];

// เพิ่ม Section ในเอกสาร
$section = $phpWord->addSection();
$section->addText('รายงานทดสอบไฟล์ Word', $titleStyle);
$section->addText('เนื้อหาในไฟล์ Word ตัวอย่าง:', $fontStyle);
$section->addText('1. ข้อความตัวอย่าง 1', $fontStyle);
$section->addText('2. ข้อความตัวอย่าง 2', $fontStyle);
$section->addText('3. ข้อความตัวอย่าง 3', $fontStyle);

// บันทึกไฟล์ชั่วคราว
$tempFilePath = sys_get_temp_dir() . '/test-file-' . time() . '.docx';
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save($tempFilePath);

// ตรวจสอบว่าไฟล์ถูกสร้างหรือไม่
if (!file_exists($tempFilePath)) {
    die('ไม่สามารถสร้างไฟล์ Word ได้');
}

// กำหนด HTTP Headers สำหรับการดาวน์โหลดไฟล์
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="test-file.docx"');
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