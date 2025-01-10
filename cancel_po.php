<?php
include('config.php');
session_start();

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $po_number = $input['po_number'] ?? null;
    
    if (!$po_number) {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบเลขที่ใบเบิก"
        ]);
        exit;
    }
    
    try {
        $stmt = $con->prepare("UPDATE po SET status = :status WHERE po_number = :po_number");
        $stmt->bindValue(':status', 'ยกเลิกใบเบิก');
        $stmt->bindValue(':po_number', $po_number);
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "ยกเลิกใบเบิกสำเร็จ"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "ไม่สามารถยกเลิกใบเบิกได้"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
