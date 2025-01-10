<?php
include('config.php');
header("Content-Type: application/json");

// ตรวจสอบว่ามีการส่ง record_number มาหรือไม่
if (isset($_GET['record_number'])) {
    $record_number = $_GET['record_number'];

    try {
        // ลบรายการที่มี record_number ตรงกับที่กำหนด
        $stmt = $con->prepare("DELETE FROM po WHERE record_number = :record_number");
        $stmt->bindParam(':record_number', $record_number);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "ลบข้อมูลสำเร็จ"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถลบข้อมูลได้"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ไม่พบหมายเลข record_number ที่ต้องการลบ"]);
}
?>
