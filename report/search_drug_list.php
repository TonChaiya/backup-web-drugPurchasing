<?php
include('../config.php'); // ตรวจสอบว่า path ถูกต้อง

try {
    $query = $_GET['query'] ?? '';
    $stmt = $con->prepare("SELECT working_code, name_item_code FROM drug_list WHERE name_item_code LIKE :query LIMIT 10");
    $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่งข้อมูลออกเป็น JSON
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
