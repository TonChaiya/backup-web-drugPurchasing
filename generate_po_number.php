<?php
include('config.php');

function generateUniqueWithdrawNumber($con) {
    $prefix = 'A';
    $latestNumber = null;

    // วนลูปจนกว่าจะได้เลขที่ไม่ซ้ำในฐานข้อมูล
    do {
        // ค้นหาเลขที่ใบเบิกล่าสุดจากฐานข้อมูล
        $stmt = $con->prepare("SELECT po_number FROM po ORDER BY po_number DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // นำหมายเลขล่าสุดมาต่อไปยังตัวเลขถัดไป
            $latestNumber = intval(substr($result['po_number'], 1)) + 1;
        } else {
            // ถ้ายังไม่มีหมายเลขในฐานข้อมูล เริ่มต้นที่ 1
            $latestNumber = 1;
        }
        
        $withdrawNumber = $prefix . str_pad($latestNumber, 2, '0', STR_PAD_LEFT);
        
        // ตรวจสอบว่ามีเลขที่ใบเบิกซ้ำหรือไม่
        $checkStmt = $con->prepare("SELECT COUNT(*) FROM po WHERE po_number = ?");
        $checkStmt->execute([$withdrawNumber]);
        $exists = $checkStmt->fetchColumn();
    } while ($exists); // วนลูปหากพบว่าเลขที่ซ้ำกันในฐานข้อมูล

    return $withdrawNumber;
}

// ส่งเลขที่ใบเบิกที่ไม่ซ้ำกลับไปยัง JavaScript
echo json_encode(["po_number" => generateUniqueWithdrawNumber($con)]);
?>
