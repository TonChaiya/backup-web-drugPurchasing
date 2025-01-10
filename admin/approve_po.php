<?php
session_start();
include('../config.php');

// รับค่าจาก URL
$po_number = $_GET['po_number'] ?? '';

if ($po_number) {
    try {
        // อัปเดตสถานะใบเบิกเป็น "อนุมัติ"
        $stmt = $con->prepare("UPDATE po SET status = 'อนุมัติ' WHERE po_number = :po_number");
        $stmt->bindParam(':po_number', $po_number, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'อนุมัติใบเบิกสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีเลขที่ใบเบิก']);
}
?>
