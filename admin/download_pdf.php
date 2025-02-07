<?php
session_start();
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    public $po_number = '';
    public $requisition_date = '';
    public $dept_name = ''; // เพิ่ม property สำหรับชื่อหน่วยงาน
    public $has_data = true; // ตั้งค่าสำหรับการแสดงหัวตารางเมื่อมีข้อมูล

    public function Header() {
        $this->SetY(15);
        $this->SetFont('thsarabunnew', 'B', 16);
        $this->Cell(0, 10, 'รายงานใบเบิกยาไตรมาสที่ 1 องค์การเภสัชกรรม GPO', 0, 1, 'C');
        $this->SetFont('thsarabunnew', '', 12);

        // แสดงชื่อหน่วยงานที่ดึงมาจากฐานข้อมูล
        $this->Cell(95, 5, 'รพ.สต : ' . $this->dept_name, 0, 0, 'L');
        $this->Cell(95, 5, 'วันที่เบิก: ' . $this->requisition_date, 0, 1, 'R');

        // เพิ่มเลขที่ใบเบิก
        $this->Cell(95, 5, 'เลขที่ใบเบิก: ' . $this->po_number, 0, 1, 'L');

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
                            <th width="10%">Lot Number</th> <!-- เพิ่มคอลัมน์ Lot Number -->
                            <th width="10%">วันหมดอายุ</th> <!-- เพิ่มคอลัมน์ Exp -->
                            <th width="10%">สถานะการจัดซื้อ</th> <!-- เพิ่มคอลัมน์ Purchase Status -->
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

include('../config.php');

$po_number = $_GET['po_number'];

// ดึงข้อมูลจากฐานข้อมูลและเรียงตาม working_code
$stmt = $con->prepare("SELECT *, DATE_FORMAT(date, '%d/%m/%Y') AS formatted_date FROM processedgpo WHERE po_number = :po_number AND purchase_status = 'GPO' ORDER BY working_code");
$stmt->bindParam(':po_number', $po_number);
$stmt->execute();
$poData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือไม่
if (!$poData) {
    die('ไม่พบข้อมูลใบเบิกหมายเลข ' . htmlspecialchars($po_number));
}

// กำหนดวันที่เบิกและชื่อหน่วยงาน
$requisition_date = $poData[0]['formatted_date'];
$dept_name = $poData[0]['dept_id']; // ดึงชื่อหน่วยงานจากคอลัมน์ dept_id

$pdf = new MYPDF();
$pdf->SetMargins(15, 48, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->po_number = $po_number;
$pdf->requisition_date = $requisition_date;
$pdf->dept_name = $dept_name; // กำหนดชื่อหน่วยงาน
$pdf->AddPage('P', 'A4');
$pdf->SetFont('thsarabunnew', '', 12);

$pdf->has_data = count($poData) > 0; // ตรวจสอบว่ามีข้อมูลหรือไม่

// สร้างตาราง
$html = '<table cellpadding="3">';
$index = 1;
$totalValue = 0;

foreach ($poData as $row) {
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
                <td width="10%" align="left">' . htmlspecialchars($row['lot_number']) . '</td> <!-- เพิ่มข้อมูล Lot Number -->
                <td width="10%" align="center">' . htmlspecialchars($row['formatted_exp'] ?? '') . '</td>
                <td width="10%" align="center">' . htmlspecialchars($row['purchase_status']) . '</td> <!-- เพิ่มข้อมูล Purchase Status -->
                <td width="15%" align="left">' . htmlspecialchars($row['remarks']) . '</td>
              </tr>';
}

$html .= '<tr>
            <td colspan="8" align="right"><strong>รวมมูลค่าทั้งหมด</strong></td>
            <td align="right"><strong>' . number_format($totalValue, 2) . '</strong></td>
            <td colspan="3"></td>
          </tr>';
$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// เพิ่มบล็อกลายเซ็นที่ท้ายกระดาษ
$pdf->SetY(-40);
$pdf->Cell(53, 10, 'ลงชื่อ........................................ผู้เบิก', 0, 0, 'C');
$pdf->Cell(53, 10, 'ลงชื่อ........................................ผู้จ่าย', 0, 0, 'C');
$pdf->Cell(53, 10, 'ลงชื่อ........................................ผู้รับของ', 0, 1, 'C');
$pdf->Cell(53, 5, '(...................................................)', 0, 0, 'C');
$pdf->Cell(53, 5, '(...................................................)', 0, 0, 'C');
$pdf->Cell(53, 5, '(...................................................)', 0, 1, 'C');

// แสดง PDF
$pdf->Output('ใบเบิก_' . $po_number . '_' . mb_convert_encoding($dept_name, 'UTF-8', 'auto') . '.pdf', 'd');

?>
