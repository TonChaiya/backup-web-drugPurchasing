<?php
require '../../vendor/autoload.php'; // โหลด autoload.php ของ PhpWord

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

try {
    // สร้างเอกสาร Word
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Hello, this is a test Word document.');

    // ตั้งค่าฮีดเดอร์สำหรับดาวน์โหลด
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="test_word.docx"');
    header('Cache-Control: max-age=0');

    // สร้างและส่งไฟล์ Word
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}