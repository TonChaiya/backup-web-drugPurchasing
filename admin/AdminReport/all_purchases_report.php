<?php
session_start();
include('../../config.php'); // ตั้งค่าฐาน


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

try {
    // รับค่าสถานะและหน่วยเบิกจาก URL
    $selectedStatus = $_GET['status'] ?? 'อนุมัติ';
    $selectedDept = $_GET['dept_id'] ?? 'all';

    // สร้าง SQL query ตามสถานะและหน่วยเบิกที่เลือก
    $where = [];
    $params = [];
    if ($selectedStatus !== 'all') {
        $where[] = 'status = :status';
        $params[':status'] = $selectedStatus;
    }
    if ($selectedDept !== 'all') {
        $where[] = 'dept_id = :dept_id';
        $params[':dept_id'] = $selectedDept;
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $sql = "SELECT po_number, date, dept_id, working_code, item_code, format_item_code, quantity, price, remarks, packing_size, status FROM po $whereSql ORDER BY date DESC";
    $stmt = $con->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // คำนวณจำนวนรายการทั้งหมดและมูลค่ารวมใหม่
    $total_rows = count($purchases); // นับจำนวนรายการ (แถว)
    $grand_total = 0; // กำหนดค่าเริ่มต้นสำหรับมูลค่ารวม

    foreach ($purchases as $key => $purchase) {
        // คำนวณมูลค่ารวมแต่ละรายการ
        $quantity = isset($purchase['quantity']) ? $purchase['quantity'] : 0;
        $price = isset($purchase['price']) ? $purchase['price'] : 0;
        $purchases[$key]['calculated_value'] = $quantity * $price; // เพิ่มคอลัมน์ใหม่
        $grand_total += $purchases[$key]['calculated_value']; // บวกเข้ากับมูลค่ารวมทั้งหมด
    }
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
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">
        รายงานจัดซื้อทั้งหมด (แยกรายการ)
        <?php if ($selectedStatus !== 'all'): ?>
            - สถานะ: <?php echo htmlspecialchars($selectedStatus); ?>
        <?php else: ?>
            - ทุกสถานะ
        <?php endif; ?>
        <?php if ($selectedDept !== 'all'): ?>
            - หน่วยเบิก: <?php echo htmlspecialchars($selectedDept); ?>
        <?php endif; ?>
    </h2>

    <?php if ($purchases): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 text-sm mx-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-left text-sm">
                        <th class="py-2 px-4 border-b">ลำดับ</th>
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
                        <th class="py-2 px-4 border-b">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($purchases as $index => $purchase): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b"><?php echo $index + 1; ?></td>
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
                            <td class="py-2 px-4 border-b"><?php echo number_format($purchase['calculated_value'], 2); ?></td>
                            <td class="py-2 px-4 border-b">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    <?php
                                    switch($purchase['status']) {
                                        case 'อนุมัติ': echo 'bg-green-100 text-green-800'; break;
                                        case 'รออนุมัติ': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'ยกเลิกใบเบิก': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($purchase['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-200">
                        <td colspan="10" class="py-2 px-4 border-t text-left text-gray-700 font-semibold">จำนวนรายการทั้งหมด:</td>
                        <td colspan="3" class="py-2 px-4 border-t text-right text-gray-700 font-semibold"><?php echo number_format($total_rows); ?></td>
                    </tr>
                    <tr class="bg-gray-200">
                        <td colspan="10" class="py-2 px-4 border-t text-left text-gray-700 font-semibold">มูลค่ารวมทั้งหมด:</td>
                        <td colspan="3" class="py-2 px-4 border-t text-right text-gray-700 font-semibold"><?php echo number_format($grand_total, 2); ?> บาท</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลจัดซื้อที่ตรงกับเงื่อนไข</p>
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