<?php
session_start();
include('../../config.php'); // ตั้งค่าฐานข้อมูล

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

try {
    // ดึงข้อมูลเฉพาะที่สถานะเป็น "อนุมัติ" จากทุกหน่วยงาน
    $stmt = $con->prepare("
        SELECT po_number, date, dept_id, working_code, item_code, format_item_code, 
               quantity, price, remarks, packing_size, total_value
        FROM po 
        WHERE status = 'ยกเลิกใบเบิก'
        ORDER BY date DESC
    ");
    $stmt->execute();
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // คำนวณจำนวนรายการทั้งหมดและมูลค่ารวม
    $total_rows = count($purchases); // นับจำนวนรายการ (แถว)
    $grand_total = array_sum(array_column($purchases, 'total_value')); // มูลค่ารวม

} catch (PDOException $e) {
    error_log($e->getMessage(), 3, '/path_to_log/error.log'); // บันทึกข้อผิดพลาด
    echo '<p class="text-red-500">Error occurred while fetching data.</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>รายงานจัดซื้อทั้งหมด</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
<div class="container mx-auto mt-8 px-4">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">รายงาน-การยกเลิก-จัดซื้อทั้งหมด</h2>
    
    <?php if ($purchases): ?>
        <table class="min-w-full bg-white border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-200 text-gray-700 text-left text-sm">
                    <th class="py-2 px-4 border-b">เลขที่ใบเบิก</th>
                    <th class="py-2 px-4 border-b">วันที่</th>
                    <th class="py-2 px-4 border-b">หน่วยเบิก</th>
                    <th class="py-2 px-4 border-b">รหัสงาน</th>
                    <th class="py-2 px-4 border-b">รหัสสินค้า</th>
                    <th class="py-2 px-4 border-b">รูปแบบสินค้า</th>
                    <th class="py-2 px-4 border-b">จำนวน</th>
                    <th class="py-2 px-4 border-b">ราคา</th>
                    <th class="py-2 px-4 border-b">หมายเหตุ</th>
                    <th class="py-2 px-4 border-b">ขนาดบรรจุ</th>
                    <th class="py-2 px-4 border-b">มูลค่ารวม</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($purchases as $purchase): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['po_number']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['date']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['dept_id']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['working_code']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['item_code']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['format_item_code']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['quantity']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['price']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['remarks']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($purchase['packing_size']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo number_format($purchase['total_value'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-200">
                    <td colspan="8" class="py-2 px-4 border-t text-left text-gray-700 font-semibold">จำนวนรายการทั้งหมด:</td>
                    <td colspan="3" class="py-2 px-4 border-t text-right text-gray-700 font-semibold"><?php echo number_format($total_rows); ?></td>
                </tr>
                <tr class="bg-gray-200">
                    <td colspan="8" class="py-2 px-4 border-t text-left text-gray-700 font-semibold">มูลค่ารวมทั้งหมด:</td>
                    <td colspan="3" class="py-2 px-4 border-t text-right text-gray-700 font-semibold"><?php echo number_format($grand_total, 2); ?> บาท</td>
                </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลจัดซื้อที่ตรงกับเงื่อนไข</p>
    <?php endif; ?>
</div>
</body>
</html>