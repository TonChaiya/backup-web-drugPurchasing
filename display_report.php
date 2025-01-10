<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['hospital_name'])) {
    header("Location: login.php");
    exit;
}

$hospital_name = $_SESSION['hospital_name'];
$reportType = $_POST['reportType'] ?? '';

try {
    switch ($reportType) {
        case 'allPurchases':
            $stmt = $con->prepare("SELECT * FROM po WHERE dept_id = :hospital_name AND status = 'อนุมัติ'");
            break;
        case 'poNumber':
            $poNumber = $_POST['poNumber'];
            $stmt = $con->prepare("SELECT * FROM po WHERE dept_id = :hospital_name AND po_number = :poNumber");
            $stmt->bindParam(':poNumber', $poNumber);
            break;
        case 'cancelledReports':
            $stmt = $con->prepare("SELECT * FROM po WHERE dept_id = :hospital_name AND status = 'ยกเลิกแล้ว'");
            break;
        case 'dateRange':
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $stmt = $con->prepare("SELECT * FROM po WHERE dept_id = :hospital_name AND date BETWEEN :startDate AND :endDate");
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            break;
        case 'medicineName':
            $medicineName = $_POST['medicineName'];
            $stmt = $con->prepare("SELECT * FROM po WHERE dept_id = :hospital_name AND medicine_name LIKE :medicineName");
            $stmt->bindParam(':medicineName', $medicineName);
            break;
        default:
            echo '<p>กรุณาเลือกประเภทการรายงานที่ถูกต้อง</p>';
            exit;
    }
    $stmt->bindParam(':hospital_name', $hospital_name);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        // Display table...
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
