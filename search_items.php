<?php
session_start();
header('Content-Type: application/json; charset=utf-8'); // ตั้งค่า header เป็น UTF-8
include 'config.php';

$query = trim($_GET['query'] ?? '');

try {
    if (empty($query)) {
        echo json_encode([], JSON_UNESCAPED_UNICODE); // ส่งค่าว่างกลับหากไม่มีคำค้นหา
        exit;
    }

    // สร้างการเชื่อมต่อ PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // ค้นหาข้อมูลจากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT name_item_code, working_code, format_item, packing_code, price_unit_code FROM drug_list WHERE name_item_code LIKE :query LIMIT 10");
    $query_param = "%$query%";
    $stmt->bindParam(':query', $query_param, PDO::PARAM_STR);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ป้องกัน XSS และจัดรูปแบบข้อมูล
    $sanitized_items = array_map(function($item) {
        return [
            'name_item_code' => htmlspecialchars($item['name_item_code'], ENT_QUOTES, 'UTF-8'),
            'working_code' => htmlspecialchars($item['working_code'], ENT_QUOTES, 'UTF-8'),
            'format_item' => htmlspecialchars($item['format_item'], ENT_QUOTES, 'UTF-8'),
            'packing_code' => htmlspecialchars($item['packing_code'], ENT_QUOTES, 'UTF-8'),
            'price_unit_code' => htmlspecialchars($item['price_unit_code'], ENT_QUOTES, 'UTF-8'),
        ];
    }, $items);

    // ส่งข้อมูลกลับเป็น JSON
    echo json_encode($sanitized_items, JSON_UNESCAPED_UNICODE); // รองรับภาษาไทย
} catch (PDOException $e) {
    // บันทึกข้อผิดพลาดลงไฟล์ log
    error_log("Database error: " . $e->getMessage());

    // ส่งข้อความข้อผิดพลาดกลับ
    echo json_encode(['error' => 'An error occurred while processing your request.'], JSON_UNESCAPED_UNICODE);
}
?>