<?php
session_start();
include('../../config.php');

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

try {
    // ดึงข้อมูลพร้อมคำนวณมูลค่ารวมแต่ละรายการ (quantity * price)
    $stmt = $con->prepare("
        SELECT 
            working_code, 
            MAX(item_code) AS item_code, 
            MAX(format_item_code) AS format_item_code, 
            SUM(CASE WHEN quantity >= 0 THEN quantity ELSE 0 END) AS total_quantity, 
            AVG(CASE WHEN price > 0 THEN price ELSE NULL END) AS average_price, 
            SUM(CASE WHEN quantity >= 0 AND price > 0 THEN quantity * price ELSE 0 END) AS total_value
        FROM po 
        WHERE status = 'อนุมัติ'
        GROUP BY working_code
        ORDER BY working_code ASC
    ");
    $stmt->execute();
    $combined_purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ตรวจสอบข้อมูลก่อนคำนวณ
    $total_items = !empty($combined_purchases) ? count($combined_purchases) : 0;

    // คำนวณมูลค่ารวมทั้งหมดใหม่ใน PHP เพื่อความแม่นยำ
    $grand_total = 0;
    if (!empty($combined_purchases)) {
        foreach ($combined_purchases as $purchase) {
            $total_value = is_numeric($purchase['total_value']) ? $purchase['total_value'] : 0;
            $grand_total += $total_value;
        }
    }

} catch (PDOException $e) {
    // บันทึกข้อผิดพลาดลง log และแจ้งเตือนผู้ใช้
    error_log($e->getMessage(), 3, '/path_to_log/error.log');
    echo '<p class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูล</p>';
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

 <!-- ความคืบหน้าการโหลด -->
 <div id="loader" class="fixed inset-0 bg-gray-500 bg-opacity-50 z-50 flex justify-center items-center">
    <div class="w-1/3 p-4 bg-white rounded-lg shadow-lg">
        <div class="text-center">
            <div class="text-gray-700 font-bold text-xl mb-4">กำลังโหลดข้อมูล...</div>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-teal-600 bg-teal-200">
                            ความคืบหน้า
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-teal-600 bg-teal-200">
                            0%
                        </span>
                    </div>
                </div>
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-teal-600 bg-teal-200">
                            การโหลดข้อมูล
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-teal-600 bg-teal-200">
                            100%
                        </span>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                <div id="progressBar" class="bg-teal-600 h-2.5 rounded-full w-0"></div>
            </div>
        </div>
    </div>
</div>


    <div class="container mx-auto mt-8 px-4">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">รายงานการเบิกทั้งหมด (รวมรายการ)</h2>

        <?php if ($combined_purchases): ?>
            <table class="min-w-full bg-white border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-left text-sm">
                        <th class="py-2 px-4 border-b text-center">ลำดับ</th>
                        <th class="py-2 px-4 border-b">รหัสงาน</th>
                        <th class="py-2 px-4 border-b">รหัสสินค้า</th>
                        <th class="py-2 px-4 border-b">รูปแบบสินค้า</th>
                        <th class="py-2 px-4 border-b text-right">จำนวนรวม</th>
                        <th class="py-2 px-4 border-b text-right">ราคาเฉลี่ย</th>
                        <th class="py-2 px-4 border-b text-right">มูลค่ารวม</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php 
                    $index = 1; // เริ่มลำดับจาก 1
                    foreach ($combined_purchases as $purchase): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b text-center"><?php echo $index++; ?></td>
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
                        <td colspan="5" class="py-2 px-4 border-t text-right font-semibold">จำนวนรายการทั้งหมด:</td>
                        <td colspan="2" class="py-2 px-4 border-t text-right font-semibold"><?php echo number_format($total_items); ?></td>
                    </tr>
                    <tr class="bg-gray-200">
                        <td colspan="5" class="py-2 px-4 border-t text-right font-semibold">มูลค่ารวมทั้งหมด:</td>
                        <td colspan="2" class="py-2 px-4 border-t text-right font-semibold"><?php echo number_format($grand_total, 2); ?> บาท</td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลการเบิกที่ตรงกับเงื่อนไข</p>
        <?php endif; ?>
    </div>

    
<!-- ปุ่ม "ขึ้นสุด" -->
<button id="scrollToTopBtn" class="fixed bottom-20 right-4 bg-green-500 text-white py-2 px-4 rounded-lg shadow-md hover:bg-green-400 focus:outline-none z-10">
    ขึ้นสุด
</button>

<!-- ปุ่ม "ลงสุด" -->
<button id="scrollToBottomBtn" class="fixed bottom-4 right-4 bg-blue-500 text-white py-2 px-4 rounded-lg shadow-md hover:bg-blue-400 focus:outline-none z-10">
    ลงสุด
</button>

<script>
    // ฟังก์ชันเลื่อนหน้าจอไปบนสุด
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // ฟังก์ชันเลื่อนหน้าจอไปล่างสุด
    function scrollToBottom() {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth'
        });
    }

    // ตั้งค่า event ให้กับปุ่ม
    document.getElementById('scrollToTopBtn').addEventListener('click', scrollToTop);
    document.getElementById('scrollToBottomBtn').addEventListener('click', scrollToBottom);
</script>

<script>
    // ฟังก์ชันแสดงความคืบหน้า
    window.onload = function() {
        var progressBar = document.getElementById('progressBar');
        var loader = document.getElementById('loader');

        // จำลองการโหลดข้อมูล
        var progress = 0;
        var interval = setInterval(function() {
            progress += 10;
            progressBar.style.width = progress + '%';
            document.querySelector('#loader .text-right span').innerText = progress + '%';
            if (progress >= 100) {
                clearInterval(interval);
                loader.style.display = 'none'; // ซ่อน loader เมื่อเสร็จสิ้น
            }
        }, 300);
    };
</script>

</body>

</html>