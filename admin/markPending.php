<?php
session_start();
include('../config.php');

// ตรวจสอบสิทธิ์ผู้ใช้ (เฉพาะ Admin เท่านั้น)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ']);
    exit;
}

// รับค่า po_number จาก POST
$data = json_decode(file_get_contents('php://input'), true);
$po_number = $data['po_number'] ?? '';

if ($po_number) {
    try {
        // อัปเดตสถานะใบเบิกเป็น 'รออนุมัติ'
        $stmt = $con->prepare("UPDATE po SET status = 'รออนุมัติ' WHERE po_number = :po_number");
        $stmt->bindParam(':po_number', $po_number, PDO::PARAM_STR);
        $stmt->execute();

        // ตรวจสอบว่ามีรายการที่อัปเดตหรือไม่
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'เปลี่ยนสถานะเป็น "รออนุมัติ" สำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่พบใบเบิกที่ระบุ']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีเลขที่ใบเบิก']);
}
?>
