<?php
include('config.php');
header("Content-Type: application/json");

if (isset($_GET['po_number'])) {
    $po_number = $_GET['po_number'];

    try {
        $stmt = $con->prepare("UPDATE po SET status = 'อนุมัติ' WHERE po_number = :po_number");
        $stmt->bindParam(':po_number', $po_number);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "ใบเบิกได้รับการอนุมัติ"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถอนุมัติใบเบิกได้"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ไม่พบเลขที่ใบเบิก"]);
}
