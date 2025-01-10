<?php
include('config.php');
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['items']) && is_array($data['items'])) {
    $po_number = $data['po_number'];
    $date = $data['date'];
    $dept_id = $data['dept_id'];

    foreach ($data['items'] as $item) {
        $working_code = $item['working_code'];
        $item_code = $item['item_code'];
        $format_item_code = $item['format_item_code'];
        $packing_size = $item['packing_size'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $total_value = $item['total_value'];
        $remarks = $item['remarks'];

        $sql = "INSERT INTO po (po_number, date, dept_id, working_code, item_code, format_item_code, packing_size, quantity, price, total_value, remarks, status) 
                VALUES (:po_number, :date, :dept_id, :working_code, :item_code, :format_item_code, :packing_size, :quantity, :price, :total_value, :remarks, 'รออนุมัติ')";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':po_number', $po_number);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':dept_id', $dept_id);
        $stmt->bindParam(':working_code', $working_code);
        $stmt->bindParam(':item_code', $item_code);
        $stmt->bindParam(':format_item_code', $format_item_code);
        $stmt->bindParam(':packing_size', $packing_size);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':total_value', $total_value);
        $stmt->bindParam(':remarks', $remarks);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "message" => "Error: " . implode(", ", $stmt->errorInfo())]);
            exit();
        }
    }

    echo json_encode(["success" => true, "message" => "บันทึกข้อมูลสำเร็จ"]);
} else {
    echo json_encode(["success" => false, "message" => "ไม่มีข้อมูลในรายการที่จะบันทึก"]);
}

function generateUniqueWithdrawNumber($conn) {
    $prefix = 'A';
    $latestNumber = null;

    // วนลูปจนกว่าจะได้เลขที่ไม่ซ้ำในฐานข้อมูล
    do {
        // ค้นหาเลขที่ใบเบิกล่าสุดจากฐานข้อมูล
        $stmt = $conn->prepare("SELECT po_number FROM po ORDER BY po_number DESC LIMIT 1");
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
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM po WHERE po_number = ?");
        $checkStmt->execute([$withdrawNumber]);
        $exists = $checkStmt->fetchColumn();
    } while ($exists); // วนลูปหากพบว่าเลขที่ซ้ำกันในฐานข้อมูล

    return $withdrawNumber;
}

?>
