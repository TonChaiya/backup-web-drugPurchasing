<?php
// processed_report.php
// รายงานข้อมูลจากตาราง processed ตาม status ที่เลือก

session_start();
include('../../config.php');

// ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// รองรับหลายสถานะ (comma separated)
$statusList = array_filter(array_map('trim', explode(',', $status)));

$params = [];
$sql = "SELECT * FROM processed";
if (!in_array('all', $statusList) && count($statusList) > 0) {
    $inClause = implode(',', array_fill(0, count($statusList), '?'));
    $sql .= " WHERE status IN ($inClause)";
    $params = $statusList;
}
$sql .= " ORDER BY processed_at DESC, id DESC";

try {
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
    $error = $e->getMessage();
}

function h($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>รายงาน processed</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-bold mb-4">รายงาน processed <?= $status !== 'all' ? ' (สถานะ: ' . h($status) . ')' : '' ?></h2>
            <a href="../admin.report.php" class="text-blue-600 hover:underline">&larr; กลับ</a>
            <?php if (!empty($error)): ?>
                <div class="text-red-600 my-4">เกิดข้อผิดพลาด: <?= h($error) ?></div>
            <?php endif; ?>
            <div class="flex justify-end mb-2">
                <a href="processed_report_excel.php?status=<?= urlencode($status) ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition" target="_blank">ดาวน์โหลด Excel</a>
            </div>
            <div class="overflow-x-auto mt-4" style="max-height: 600px; overflow-y: auto;">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-2 py-1 border">ID</th>
                            <th class="px-2 py-1 border">Working Code</th>
                            <th class="px-2 py-1 border">Item Code</th>
                            <th class="px-2 py-1 border">Format Item Code</th>
                            <th class="px-2 py-1 border">Total Qty</th>
                            <th class="px-2 py-1 border">Price</th>
                            <th class="px-2 py-1 border">Packing Size</th>
                            <th class="px-2 py-1 border">Total Value</th>
                            <th class="px-2 py-1 border">Status</th>
                            <th class="px-2 py-1 border">Purchase Status</th>
                            <th class="px-2 py-1 border">Processed At</th>
                            <th class="px-2 py-1 border">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="12" class="text-center py-4">ไม่พบข้อมูล</td></tr>
                        <?php else: ?>
                            <?php 
                                $sum_qty = 0;
                                $sum_value = 0;
                                $item_count = 0;
                                foreach ($rows as $row): 
                                    $sum_qty += (float)$row['total_quantity'];
                                    $sum_value += (float)$row['total_value'];
                                    $item_count++;
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border px-2 py-1"><?= h($row['id']) ?></td>
                                    <td class="border px-2 py-1"><?= h($row['working_code']) ?></td>
                                    <td class="border px-2 py-1"><?= h($row['item_code']) ?></td>
                                    <td class="border px-2 py-1"><?= h($row['format_item_code']) ?></td>
                                    <td class="border px-2 py-1 text-right"><?= h($row['total_quantity']) ?></td>
                                    <td class="border px-2 py-1 text-right"><?= h($row['price']) ?></td>
                                    <td class="border px-2 py-1"><?= h($row['packing_size']) ?></td>
                                    <td class="border px-2 py-1 text-right"><?= h($row['total_value']) ?></td>
                                    <td class="border px-2 py-1"><?= h($row['status']) ?></td>
                                    <td class="border px-2 py-1"><?= h($row['purchase_status']) ?></td>
                                    <td class="border px-2 py-1 text-xs"><?= h($row['processed_at']) ?></td>
                                    <td class="border px-2 py-1 text-xs"><?= h($row['remarks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- summary row -->
                            <tr class="font-bold bg-yellow-100">
                                <td class="border px-2 py-1 text-right" colspan="4">รวม</td>
                                <td class="border px-2 py-1 text-right"><?= number_format($sum_qty) ?></td>
                                <td class="border px-2 py-1"></td>
                                <td class="border px-2 py-1"></td>
                                <td class="border px-2 py-1 text-right"><?= number_format($sum_value, 2) ?></td>
                                <td class="border px-2 py-1 text-right" colspan="4">จำนวนรายการ: <?= number_format($item_count) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
