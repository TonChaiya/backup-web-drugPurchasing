<?php
session_start();
include('../../config.php'); // ตั้งค่าฐานข้อมูล

// ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ตรวจสอบว่าเลขที่ใบเบิกถูกส่งมาหรือไม่
if (!isset($_GET['po_number'])) {
    echo '<div class="text-red-500 text-center mt-8">กรุณาระบุเลขที่ใบเบิก</div>';
    exit;
}

$po_number = $_GET['po_number'];

try {
    // ดึงข้อมูลตามเลขที่ใบเบิกที่กำหนด
    $stmt = $con->prepare("
        SELECT po_number, date, dept_id, working_code, item_code, format_item_code, 
               quantity, price, remarks, packing_size, total_value
        FROM po 
        WHERE po_number = :po_number AND status = 'อนุมัติ'
    ");
    $stmt->bindParam(':po_number', $po_number);
    $stmt->execute();
    $po_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // คำนวณจำนวนรายการและมูลค่ารวม
    $total_items = count($po_records);
    $grand_total = array_sum(array_column($po_records, 'total_value'));
} catch (PDOException $e) {
    echo '<div class="text-red-500 text-center mt-8">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานตามเลขที่ใบเบิก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto mt-10 px-4">
        <h1 class="text-3xl font-bold text-center text-gray-700 mb-6">
            รายงานตามเลขที่ใบเบิก: <?php echo htmlspecialchars($po_number); ?>
        </h1>

        <?php if ($po_records): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden border border-gray-300">
                    <thead class="bg-gray-200">
                        <tr>
                            <?php
                            $headers = ['เลขที่ใบเบิก', 'วันที่', 'หน่วยเบิก', 'รหัสงาน', 'รหัสสินค้า', 'รูปแบบสินค้า', 'จำนวน', 'ราคา', 'หมายเหตุ', 'ขนาดบรรจุ', 'มูลค่ารวม'];
                            foreach ($headers as $header) {
                                echo "<th class='py-3 px-4 text-gray-700 border-b font-semibold text-sm'>{$header}</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($po_records as $record): ?>
                            <tr class="hover:bg-gray-100">
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['po_number']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['date']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['dept_id']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['working_code']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['item_code']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['format_item_code']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['quantity']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['price']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['remarks']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($record['packing_size']); ?></td>
                                <td class="py-3 px-4 border-b text-right"><?php echo number_format($record['total_value'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-200">
                        <tr>
                            <td colspan="9" class="py-3 px-4 text-gray-700 font-semibold">จำนวนรายการทั้งหมด</td>
                            <td colspan="2" class="py-3 px-4 text-right text-gray-700 font-semibold"><?php echo $total_items; ?></td>
                        </tr>
                        <tr>
                            <td colspan="9" class="py-3 px-4 text-gray-700 font-semibold">มูลค่ารวมทั้งหมด</td>
                            <td colspan="2" class="py-3 px-4 text-right text-gray-700 font-semibold"><?php echo number_format($grand_total, 2); ?> บาท</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-gray-500 mt-6">
                ไม่พบข้อมูลสำหรับเลขที่ใบเบิกนี้
            </div>
        <?php endif; ?>
    </div>
</body>

</html>