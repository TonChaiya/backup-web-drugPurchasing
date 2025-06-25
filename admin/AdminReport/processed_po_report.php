<?php
// รายงานข้อมูล po_processed ตามสถานะและ purchase_status
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$purchase_status = isset($_GET['purchase_status']) ? $_GET['purchase_status'] : 'all';

$where = [];
$params = [];
if ($status !== 'all') {
    $where[] = 'status = :status';
    $params[':status'] = $status;
}
if ($purchase_status !== 'all') {
    $where[] = 'purchase_status = :purchase_status';
    $params[':purchase_status'] = $purchase_status;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

try {
    $sql = "SELECT * FROM po_processed $whereSql ORDER BY date DESC, po_number DESC";
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()));
}

function h($str) { return htmlspecialchars($str); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานข้อมูล po_processed</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-8">
        <h2 class="text-2xl font-bold mb-4">รายงานข้อมูล po_processed (กรองตามสถานะ)</h2>
        <div class="mb-4">
            <a href="../admin.report.php" class="text-blue-600 hover:underline">&larr; กลับหน้าเลือกประเภทของรายงาน</a>
        </div>
        <div class="bg-white rounded shadow p-4 overflow-x-auto">
            <div class="flex flex-wrap gap-4 mb-4">
                <a href="export_po_processed_excel.php?status=<?=urlencode($status)?>&purchase_status=<?=urlencode($purchase_status)?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition" target="_blank">ดาวน์โหลด Excel รวม</a>
                <a href="export_po_processed_by_dept_excel.php?status=<?=urlencode($status)?>&purchase_status=<?=urlencode($purchase_status)?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition" target="_blank">ดาวน์โหลด Excel แยกชีทตามหน่วยเบิก</a>
            </div>
            <table class="min-w-full text-xs md:text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-2 py-1">#</th>
                        <th class="px-2 py-1">เลขที่ใบเบิก</th>
                        <th class="px-2 py-1">วันที่</th>
                        <th class="px-2 py-1">หน่วยเบิก</th>
                        <th class="px-2 py-1">working_code</th>
                        <th class="px-2 py-1">item_code</th>
                        <th class="px-2 py-1">format_item_code</th>
                        <th class="px-2 py-1">จำนวน</th>
                        <th class="px-2 py-1">ราคา</th>
                        <th class="px-2 py-1">หมายเหตุ</th>
                        <th class="px-2 py-1">packing_size</th>
                        <th class="px-2 py-1">total_value</th>
                        <th class="px-2 py-1">status</th>
                        <th class="px-2 py-1">purchase_status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) === 0): ?>
                        <tr><td colspan="14" class="text-center py-4 text-gray-500">ไม่พบข้อมูล</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $row): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-2 py-1 text-center"><?= $i+1 ?></td>
                                <td class="px-2 py-1"><?= h($row['po_number']) ?></td>
                                <td class="px-2 py-1"><?= h($row['date']) ?></td>
                                <td class="px-2 py-1"><?= h($row['dept_id']) ?></td>
                                <td class="px-2 py-1"><?= h($row['working_code']) ?></td>
                                <td class="px-2 py-1"><?= h($row['item_code']) ?></td>
                                <td class="px-2 py-1"><?= h($row['format_item_code']) ?></td>
                                <td class="px-2 py-1 text-right"><?= h($row['quantity']) ?></td>
                                <td class="px-2 py-1 text-right"><?= h($row['price']) ?></td>
                                <td class="px-2 py-1"><?= h($row['remarks']) ?></td>
                                <td class="px-2 py-1"><?= h($row['packing_size']) ?></td>
                                <td class="px-2 py-1 text-right"><?= h($row['total_value']) ?></td>
                                <td class="px-2 py-1"><?= h($row['status']) ?></td>
                                <td class="px-2 py-1"><?= h($row['purchase_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-gray-600">รวมทั้งหมด <?= count($rows) ?> รายการ</div>
    </div>
</body>
</html>
