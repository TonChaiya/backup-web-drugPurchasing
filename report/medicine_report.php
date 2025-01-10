<?php
session_start();
include('../config.php');

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id']) || !isset($_SESSION['hospital_name'])) {
    header("Location: ../login.php");
    exit;
}

// รับค่าจาก `working_code` ที่ส่งมา
$workingCode = $_GET['working_code'] ?? null;

// ดึงค่า `hospital_name` จากเซสชัน
$hospital_name = $_SESSION['hospital_name'];

if ($workingCode) {
    try {
        // SQL ดึงข้อมูลจากตาราง `po` โดยกรองตาม `working_code`, `dept_id`, และ `status`
        $stmt = $con->prepare("
            SELECT po_number, date, dept_id, working_code, item_code, format_item_code, 
                   quantity, price, remarks, packing_size, total_value, status 
            FROM po 
            WHERE working_code = :working_code 
              AND dept_id = :hospital_name
              AND status = 'อนุมัติ'
        ");
        $stmt->bindValue(':working_code', $workingCode, PDO::PARAM_STR);
        $stmt->bindValue(':hospital_name', $hospital_name, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // เริ่มต้นโครงสร้างของหน้า
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>รายงานรายการยา</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">';
        echo '</head>';
        echo '<body class="bg-gray-100 font-sans leading-normal tracking-normal">';

        echo '<div class="max-w-6xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">';
        echo '<h2 class="text-3xl font-bold mb-6 text-center text-gray-800">รายงานรายการยา</h2>';

        if ($result) {
            // ตารางรายงานข้อมูล
            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">';
            echo '<thead class="bg-indigo-600 text-white">';
            echo '<tr>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">เลขที่ใบเบิก</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">วันที่</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">หน่วยงาน</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">รหัสยา</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">รหัสรายการยา</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">รูปแบบยา</th>';
            echo '<th class="px-4 py-3 border text-right text-sm font-semibold">จำนวน</th>';
            echo '<th class="px-4 py-3 border text-right text-sm font-semibold">ราคา</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">หมายเหตุ</th>';
            echo '<th class="px-4 py-3 border text-left text-sm font-semibold">ขนาดบรรจุ</th>';
            echo '<th class="px-4 py-3 border text-right text-sm font-semibold">มูลค่ารวม</th>';
            echo '</tr>';
            echo '</thead>';

            echo '<tbody class="bg-white divide-y divide-gray-200">';
            foreach ($result as $row) {
                echo '<tr class="hover:bg-gray-100">';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['po_number']) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['date']) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['dept_id']) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['working_code']) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['item_code']) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['format_item_code']) . '</td>';
                echo '<td class="px-4 py-3 border text-right text-sm text-gray-700">' . htmlspecialchars($row['quantity']) . '</td>';
                echo '<td class="px-4 py-3 border text-right text-sm text-gray-700">' . htmlspecialchars(number_format($row['price'], 2)) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['remarks']) . '</td>';
                echo '<td class="px-4 py-3 border text-sm text-gray-700">' . htmlspecialchars($row['packing_size']) . '</td>';
                echo '<td class="px-4 py-3 border text-right text-sm text-gray-700">' . htmlspecialchars(number_format($row['total_value'], 2)) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            // สรุปข้อมูลด้านล่างตาราง
            $totalItems = count($result);
            $totalValue = array_sum(array_column($result, 'total_value'));

            echo '<div class="flex justify-between mt-4 px-4">';
            echo '<span class="text-gray-800 text-lg font-medium">จำนวนรายการทั้งหมด: ' . $totalItems . ' รายการ</span>';
            echo '<span class="text-gray-800 text-lg font-medium">มูลค่ารวมทั้งหมด: ' . number_format($totalValue, 2) . ' บาท</span>';
            echo '</div>';
        } else {
            echo '<p class="text-center text-red-500 mt-4">ไม่พบข้อมูลสำหรับรหัสยาที่ระบุ</p>';
        }

        echo '</div>'; // ปิด div ของตาราง
        echo '</body>';
        echo '</html>';
    } catch (PDOException $e) {
        echo "<p class='text-red-500'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='text-center text-red-500'>ไม่พบรหัสยาที่ส่งมา</p>";
}
