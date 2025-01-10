<?php 
session_start();
include 'config.php';

$query = trim($_GET['query'] ?? '');

try {
    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // เพิ่มการดึงราคาด้วยใน SQL
    $stmt = $pdo->prepare("SELECT name_item_code, working_code, format_item, packing_code, price_unit_code FROM drug_list WHERE name_item_code LIKE :query LIMIT 10");
    $query_param = "%$query%";
    $stmt->bindParam(':query', $query_param, PDO::PARAM_STR);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ป้องกัน XSS และเพิ่มราคาในข้อมูลที่ส่งกลับ
    $sanitized_items = array_map(function($item) {
        return [
            'name_item_code' => htmlspecialchars($item['name_item_code'], ENT_QUOTES, 'UTF-8'),
            'working_code' => htmlspecialchars($item['working_code'], ENT_QUOTES, 'UTF-8'),
            'format_item' => htmlspecialchars($item['format_item'], ENT_QUOTES, 'UTF-8'),
            'packing_code' => htmlspecialchars($item['packing_code'], ENT_QUOTES, 'UTF-8'),
            'price_unit_code' => htmlspecialchars($item['price_unit_code'], ENT_QUOTES, 'UTF-8'),
        ];
    }, $items);

    echo json_encode($sanitized_items);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request.']);
}
