<?php
session_start();
require_once __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

class MYPDF extends TCPDF {
    public $po_number = '';
    public $has_data = true; // ตั้งค่าสำหรับการแสดงหัวตารางเมื่อมีข้อมูล

    public function Header() {
        // ส่วนหัวรายงาน
        $this->SetY(15); 
        $this->SetFont('thsarabunnew', 'B', 16);
        $this->Cell(0, 10, 'รายงานใบเบิกยา', 0, 1, 'C');
        $this->SetFont('thsarabunnew', '', 12);

        // แสดงเลขที่ใบเบิกและข้อมูลโรงพยาบาล
        $this->Cell(0, 5, 'เลขที่ใบเบิก: ' . $this->po_number, 0, 1, 'L');
        $hospital_name = isset($_SESSION['hospital_name']) ? $_SESSION['hospital_name'] : 'ไม่ระบุโรงพยาบาล';
        $this->Cell(95, 5, 'รพ.สต : ' . $hospital_name, 0, 0, 'L');
        $this->Cell(95, 5, 'วันที่เบิก: ' . date('Y-m-d H:i:s'), 0, 1, 'R');
        $this->Ln(5);

        // แสดงหัวตารางถ้ามีข้อมูล
        if ($this->has_data) {
            $html = '<style>
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #000; padding: 3px; }
                     </style>';
            $html .= '<table cellpadding="3">
                        <tr>
                            <th width="5%">ลำดับ</th>
                            <th width="10%">รหัสสินค้า</th>
                            <th width="20%">ชื่อยา</th>
                            <th width="10%">รูปแบบ</th>
                            <th width="15%">ขนาดบรรจุ</th>
                            <th width="7%">จำนวน</th>
                            <th width="10%">ราคา</th>
                            <th width="10%">มูลค่ารวม</th>
                            <th width="15%">หมายเหตุ</th>
                        </tr>';
            $this->writeHTML($html, true, false, false, false, '');
        }
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('thsarabunnew', '', 10);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF();
$pdf->SetMargins(15, 48, 10);
$pdf->SetAutoPageBreak(true, 15);

$po_number = $_GET['po_number'];
$pdf->po_number = $po_number;

$pdf->AddPage('P', 'A4');
$pdf->SetFont('thsarabunnew', '', 12);

include('config.php');

// ดึงข้อมูลจากฐานข้อมูล
$stmt = $con->prepare("SELECT * FROM po WHERE po_number = :po_number");
$stmt->bindParam(':po_number', $po_number);
$stmt->execute();
$poData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf->has_data = count($poData) > 0; // ตรวจสอบว่ามีข้อมูลหรือไม่

// สร้างตาราง
$html = '<table cellpadding="3">';
$index = 1;
$totalValue = 0;
$rows_per_page = 30;
$current_row = 0;

foreach ($poData as $row) {
    if ($current_row > 0 && $current_row % $rows_per_page === 0) {
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // ตรวจสอบพื้นที่ก่อนเพิ่มเนื้อหาใหม่
        if ($pdf->GetY() + 50 > $pdf->getPageHeight() - 30) {
            $pdf->AddPage(); // เพิ่มหน้าใหม่หากพื้นที่ไม่เพียงพอ
        }

        $html = '<table cellpadding="3">';
    }

    $totalValue += $row['total_value'];
    $html .= '<tr>
                <td width="5%" align="center">' . $index++ . '</td>
                <td width="10%" align="center">' . htmlspecialchars($row['working_code']) . '</td>
                <td width="20%" align="left">' . htmlspecialchars($row['item_code']) . '</td>
                <td width="10%" align="center">' . htmlspecialchars($row['format_item_code']) . '</td>
                <td width="15%" align="center">' . htmlspecialchars($row['packing_size']) . '</td>
                <td width="7%" align="right">' . number_format($row['quantity']) . '</td>
                <td width="10%" align="right">' . number_format($row['price'], 2) . '</td>
                <td width="10%" align="right">' . number_format($row['total_value'], 2) . '</td>
                <td width="15%" align="left">' . htmlspecialchars($row['remarks']) . '</td>
              </tr>';
    $current_row++;
}

// ปิดตารางและเขียน HTML ของหน้าสุดท้าย
if ($current_row > 0) {
    $html .= '<tr>
                <td colspan="7" align="right"><strong>รวมมูลค่าทั้งหมด</strong></td>
                <td align="right"><strong>' . number_format($totalValue, 2) . '</strong></td>
                <td></td>
              </tr>';
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
}

// ตรวจสอบพื้นที่และสร้างหน้าสำหรับลายเซ็นถ้าจำเป็น
if ($pdf->GetY() + 50 > $pdf->getPageHeight() - 30 || ($current_row > 0 && $current_row % $rows_per_page === 0)) {
    $pdf->AddPage();
}

// เพิ่มบล็อกลายเซ็นที่ท้ายกระดาษ
$pdf->SetY(-40); // ตั้งค่าตำแหน่งให้ลายเซ็นอยู่ใกล้ท้ายกระดาษเสมอ
$pdf->Cell(80, 10, 'ลงชื่อ........................................ผู้เบิก', 0, 0, 'C');
$pdf->Cell(80, 10, 'ลงชื่อ........................................ผู้รับรอง', 0, 1, 'C');
$pdf->Cell(80, 5, '(...................................................)', 0, 0, 'C');
$pdf->Cell(80, 5, '(...................................................)', 0, 1, 'C');

// แสดง PDF
$pdf->Output('ใบเบิก_' . $po_number . '.pdf', 'I');
?>
