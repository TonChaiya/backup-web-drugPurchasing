<?php
require dirname(__DIR__) . '../../config.php';
require dirname(__DIR__) . '../../vendor/autoload.php'; // ใช้สำหรับการสร้าง PDF

// สร้างเอกสาร PDF ใหม่
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ปิดการแสดงส่วนหัวและส่วนท้าย
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// ตั้งค่าข้อมูลเอกสาร
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Note PDF');

// ตั้งค่าแบบอักษร
$pdf->SetFont('thsarabunit', '', 16);

// ตั้งค่าระยะขอบและช่องไฟ
$pdf->SetMargins(25, 15, 20); // ซ้าย 2.5 ซม., บน 1.5 ซม., ขวา 2 ซม.
$pdf->SetAutoPageBreak(TRUE, 20); // ล่าง 2 ซม.

// เพิ่มหน้า
$pdf->AddPage();

// เพิ่มรูปภาพที่มุมบนซ้าย
$imagePath = '../Purchasing/logo.png'; // เส้นทางของรูปภาพ
if (file_exists($imagePath)) {
    $pdf->Image($imagePath, 25, 10, 15, 15, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
} else {
    $pdf->SetY(10);
    $pdf->SetTextColor(255, 0, 0); // สีแดง
    $pdf->Cell(0, 10, 'ไม่พบโลโก้', 0, 1, 'L');
}

// เพิ่มข้อความหัวข้อ
$pdf->SetFont('thsarabunit', 'B', 21);
$pdf->SetY(15);
$pdf->Cell(0, 15, 'บันทึกข้อความ', 0, 1, 'C');

// เพิ่มข้อความส่วนราชการ
$pdf->SetFont('thsarabunit', '', 16);
$pdf->SetY(30);

$html = <<<HTML
<strong style="font-size: 20px;">ส่วนราชการ</strong> กองสาธารณสุข (ฝ่ายบริหารงานสาธารณสุข) โทร. 053-596516 ต่อ 601 - 602
<br><strong style="font-size: 20px;">ที่ </strong>ลพ.51010.05/
<br><strong style="font-size: 20px;">เรื่อง</strong>
<br><strong style="font-size: 20px;">เรียน</strong> นายกองค์การบริหารส่วนจังหวัดลำพูน
HTML;
$pdf->writeHTMLCell(0, 0, 25, '', $html, 0, 1, 0, true, 'L', true);

$html = <<<HTML
<p style="text-align: justify; text-indent: 40px; word-spacing: 1px;">
1.คำสั่งองค์การบริหารส่วนจังหวัดลำพูนที่.........../..........ลงวันที่ ..................................เรื่องแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะและราคากลางเพื่อจัดซื้อเวชภัณฑ์ยาสนับสนุนการให้บริการสาธารณะระดับปฐมภูมิของโรงพยาบาลส่งเสริมสุขภาพตำบลในสังกัดองค์การบริหารส่วนจังหวัดลำพูนประจำเดือนตุลาคม 2567 - เดือนธันวาคม 2567 ประกอบด้วย
</p>
HTML;
$pdf->writeHTML($html, true, false, true, false, '');


// เพิ่มตาราง
$pdf->SetY($pdf->GetY() + 2); // เพิ่มระยะห่างก่อนตาราง
$col_width = array(15, 50, 70, 30);

// หัวข้อในตาราง
// $pdf->SetFont('thsarabunit', 'B', 16);
// $pdf->SetX(25);
// $pdf->MultiCell($col_width[0], 5, "ลำดับ", 0, 'C', 0, 0);
// $pdf->MultiCell($col_width[1], 5, "ชื่อ", 0, 'C', 0, 0);
// $pdf->MultiCell($col_width[2], 5, "ตำแหน่ง", 0, 'C', 0, 0);
// $pdf->MultiCell($col_width[3], 5, "สถานะ", 0, 'C', 0, 1);

// ข้อมูลในตาราง
$pdf->SetFont('thsarabunit', '', 16);
$data = [
    ['1.', 'นางสมจิต ตันแปง', 'พยาบาลวิชาชีพชำนาญการ', 'ประธานกรรมการ'],
    ['', '', 'รพ.สต.บ้านมงคลชัย', ''],
    ['2.', 'นายนรงค์ชัย ใจยา', 'เจ้าพนักงานเภสัชกรรมชำนาญงาน', 'กรรมการ'],
    ['', '', 'รพ.สต.บ้านมงคลชัย', ''],
    ['3.', 'นายไชยา บุญทานุช', 'เจ้าพนักงานเภสัชกรรมปฏิบัติงาน', 'กรรมการ'],
    ['', '', 'รพ.สต.บ้านมงคลชัย', ''],
];

foreach ($data as $row) {
    $pdf->SetX(25);
    foreach ($row as $key => $text) {
        $pdf->MultiCell($col_width[$key], 5, $text, 0, $key === 0 ? 'C' : 'L', 0, 0);
    }
    $pdf->Ln(); // ลงบรรทัดใหม่
}

// ปิดและแสดงเอกสาร PDF
$pdf->Output('note.pdf', 'I');
?>
