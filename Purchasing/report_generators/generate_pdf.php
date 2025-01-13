<?php
require dirname(__DIR__) . '../../config.php';
require dirname(__DIR__) . '../../vendor/autoload.php'; // ใช้สำหรับการสร้าง PDF

$status = $_GET['status'];

// ดึงข้อมูลจากตาราง processed ที่มีสถานะ purchase_status = :status
$query = "SELECT * FROM processed WHERE purchase_status = :status ORDER BY working_code";
$stmt = $con->prepare($query);
$stmt->execute([':status' => $status]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

class CustomPDF extends TCPDF {
    // ปรับแต่ง Header
    public function Header() {
        // ตรวจสอบว่าหน้านี้ไม่ใช่หน้าแรก (หน้าที่ 1)
        if ($this->getPage() > 1) {
            // เพิ่มหมายเลขหน้าตรงกลางด้านบนสุด
            $this->SetY(15); // ปรับระยะห่างจากขอบบน
            $this->SetFont('thsarabunit', '', 16);
            $this->Cell(0, 10, $this->getAliasNumPage(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            // เลื่อนข้อความที่เหลือลงมาอีก 1 บรรทัด
            $this->Ln(20); // เพิ่มระยะห่างหลังจากหมายเลขหน้า
        }
    }

    // ปิด Footer
    public function Footer() {}
}

$pdf = new CustomPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('รายงานสถานะ: ' . $status);
$pdf->SetSubject('รายงานสถานะ');
$pdf->SetKeywords('TCPDF, PDF, report');

// ปิดเส้น Header และ Footer
$pdf->setPrintHeader(true); // เปิดการแสดง Header
$pdf->setPrintFooter(false);

// ลบข้อมูล Header Line
$pdf->SetHeaderData('', '', '', '');

// ตั้งค่าระยะขอบกระดาษ (หน่วยเป็นมิลลิเมตร)
$top_margin = 15;   // ระยะขอบบน (1.5 cm)
$left_margin = 25;  // ระยะขอบซ้าย (2.5 cm)
$right_margin = 5; // ระยะขอบขวา (2 cm)
$bottom_margin = 10; // ระยะขอบล่าง (2 cm)

$pdf->SetMargins($left_margin, $top_margin, $right_margin);
$pdf->SetHeaderMargin(5);  // ระยะขอบสำหรับ Header
$pdf->SetFooterMargin($bottom_margin);
$pdf->SetAutoPageBreak(TRUE, $bottom_margin);

// เพิ่มหน้า PDF แรก
$pdf->AddPage();

// ตั้งค่าฟอนต์
$pdf->SetFont('thsarabunit', '', 16); // ใช้ฟอนต์ Sarabun

$html = <<<HTML
<strong style="font-size: 16px;">รายละเอียดคุณลักษณะของพัสดุที่จะซื้อหรือจ้าง</strong>
<br><strong style="font-size: 16px;">ตามระเบียบกระทรวงการคลังว่าด้วยการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐ ข้อ ๒๑</strong>
<br><strong style="font-size: 16px;">งานจัดซื้อเวชภัณฑ์ยา สนับสนุนการให้บริการสาธารณะระดับปฐมภูมิ</strong>
<br><strong style="font-size: 16px;">ของโรงพยาบาลส่งเสริมสุขภาพตำบลในสังกัดองค์การบริหารส่วนจังหวัดลำพูน</strong>
<br><strong style="font-size: 16px;">ประจำเดือนตุลาคม ๒๕๖๗ - เดือนธันวาคม ๒๕๖๗</strong>
HTML;
$pdf->writeHTMLCell(0, 0, 25, '', $html, 0, 1, 0, true, 'C', true);



// เพิ่มหน้าต่อไปสำหรับข้อมูลจากฐานข้อมูล
$pdf->AddPage();

// ตั้งค่าฟอนต์สำหรับข้อมูลจากฐานข้อมูล
$pdf->SetFont('thsarabunit', '', 16);

// เริ่มสร้างเนื้อหาจากฐานข้อมูล
$html = '<br><br><br>'; // เพิ่มบรรทัดว่าง 2 บรรทัด
$html .= '<table border="0" cellpadding="5">';
$index = 1;  // เริ่มนับลำดับที่
$item_count = 0; // ตัวนับรายการต่อหน้า

foreach ($data as $row) {
    if ($item_count == 3) { // เปลี่ยนจาก 4 เป็น 3
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->AddPage();
        $html = '<br><br><br>'; // เพิ่มบรรทัดว่าง 2 บรรทัด
        $html .= '<table border="0" cellpadding="5">';
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
    $html .= '<tr>';
    $html .= '<td style="width: 70%; text-align: left; line-height: 0.3;"><strong>ลำดับที่</strong> ' . $index++ . ' <strong>ชื่อยา</strong> ' . $medicine_name . '</td>';
    $html .= '<td style="width: 20%; text-align: right; line-height: 0.3;"><strong>จำนวน</strong> ' . $total_quantity . ' ' . $format_item_code . '</td>';
    $html .= '</tr>';

    // แถวที่ 2: ขนาดบรรจุ
    $html .= '<tr>';
    $html .= '<td colspan="2" style="text-align: left; line-height: 0.4;"><strong>ขนาดบรรจุ:</strong>'.'  ' . $packing_size . '</td>';
    $html .= '</tr>';
    
    // แถวที่ 3: รูปแบบ 
    $medicine_query = "SELECT format_itemcode FROM medicine_info WHERE working_code = :working_code";
    $medicine_stmt = $con->prepare($medicine_query);
    $medicine_stmt->execute([':working_code' => $row['working_code']]);
    $medicine_info = $medicine_stmt->fetch(PDO::FETCH_ASSOC);

    // แสดงข้อมูล format_itemcode
    $html .= '<tr>';
    $html .= '<td colspan="2" style="text-align: left; line-height: 0.4;"><strong>รูปแบบ:</strong> ' . htmlspecialchars($medicine_info['format_itemcode']) . '</td>';
    $html .= '</tr>';
    
    // แถวที่ 4: ประกอบด้วยตัวยา
    $html .= '<tr>';
    $html .= '<td colspan="2" style="text-align: left; line-height: 0.4;"><strong>ประกอบด้วยตัวยา</strong> ' . $item_code . '</td>';
    $html .= '</tr>';
    
    // แถวที่ 5: ภาชนะบรรจุ
    $html .= '<tr>';
    $html .= '<td colspan="2" style="text-align: left; line-height: 0.4;"><strong>ภาชนะบรรจุบรรจุ</strong> ' . '</td>';
    $html .= '</tr>';
    
    // แถวที่ 6: ภาชนะบรรจุ + ข้อความเพิ่มเติม
    $container_query = "SELECT container1 FROM medicine_info WHERE working_code = :working_code";
    $container_stmt = $con->prepare($container_query);
    $container_stmt->execute([':working_code' => $row['working_code']]);
    $container_info = $container_stmt->fetch(PDO::FETCH_ASSOC);

    $container_text = htmlspecialchars($container_info['container1']);
    $container_text = str_replace("\n", "<br>", $container_text);
    $html .= '<tr>';
    $html .= '<td colspan="2" style="text-align: left; line-height: 1.2; font-size: 5mm;">' . $container_text . '</td>';
    $html .= '</tr>';

    // แถวที่ 7: ว่างไว้เพื่อคั่นรายการ
    $html .= '<tr>';
    $html .= '<td colspan="2" style="text-align: left; line-height: 1;"> </td>';
    $html .= '</tr>';

    $item_count++;
}
$html .= '</table>';

// เพิ่มเนื้อหา HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// ส่งไฟล์ PDF ให้ผู้ใช้ดาวน์โหลดในแท็บใหม่
$pdf->Output('report.pdf', 'I');
?>