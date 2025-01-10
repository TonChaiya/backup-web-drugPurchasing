<?php
session_start();
include('../../config.php');

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

try {
    // ดึงข้อมูลรวมตาม working_code
    $stmt = $con->prepare("
        SELECT working_code, 
               MAX(item_code) AS item_code, 
               MAX(format_item_code) AS format_item_code, 
               SUM(quantity) AS total_quantity, 
               AVG(price) AS average_price, 
               SUM(total_value) AS total_value
        FROM po 
        WHERE status = 'อนุมัติ'
        GROUP BY working_code
        ORDER BY item_code ASC
    ");
    $stmt->execute();
    $combined_purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // นับจำนวนรายการทั้งหมด
    $total_items = count($combined_purchases); // นับจำนวนแถว
    $grand_total = array_sum(array_column($combined_purchases, 'total_value')); // มูลค่ารวม
} catch (PDOException $e) {
    error_log($e->getMessage(), 3, '/path_to_log/error.log');
    echo '<p class="text-red-500">Error occurred while fetching data.</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>รายงานการเบิกทั้งหมด (รวมรายการ)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mx-auto mt-8 px-4">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">รายงานการเบิกทั้งหมด (รวมรายการ)</h2>

        <?php if ($combined_purchases): ?>
            <table class="min-w-full bg-white border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-left text-sm">
                        <th class="py-2 px-4 border-b">รหัสงาน</th>
                        <th class="py-2 px-4 border-b">รหัสสินค้า</th>
                        <th class="py-2 px-4 border-b">รูปแบบสินค้า</th>
                        <th class="py-2 px-4 border-b text-right">จำนวนรวม</th>
                        <th class="py-2 px-4 border-b text-right">ราคาเฉลี่ย</th>
                        <th class="py-2 px-4 border-b text-right">มูลค่ารวม</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($combined_purchases as $purchase): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['working_code'] ?? '-'); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['item_code'] ?? '-'); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['format_item_code'] ?? '-'); ?></td>
                            <td class="py-2 px-4 border-b text-right"><?php echo number_format($purchase['total_quantity']); ?></td>
                            <td class="py-2 px-4 border-b text-right"><?php echo number_format($purchase['average_price'], 2); ?></td>
                            <td class="py-2 px-4 border-b text-right"><?php echo number_format($purchase['total_value'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-200">
                        <td colspan="4" class="py-2 px-4 border-t text-right font-semibold">จำนวนรายการทั้งหมด:</td>
                        <td colspan="2" class="py-2 px-4 border-t text-right font-semibold"><?php echo number_format($total_items); ?></td>
                    </tr>
                    <tr class="bg-gray-200">
                        <td colspan="4" class="py-2 px-4 border-t text-right font-semibold">มูลค่ารวมทั้งหมด:</td>
                        <td colspan="2" class="py-2 px-4 border-t text-right font-semibold"><?php echo number_format($grand_total, 2); ?> บาท</td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลการเบิกที่ตรงกับเงื่อนไข</p>
        <?php endif; ?>
    </div>
</body>

</html>