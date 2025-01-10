<?php
include('../config.php'); // ใช้ ../config.php เนื่องจากไฟล์อยู่ใน /admin
session_start();

// ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['status'])) {
    $status = $_GET['status'];

    try {
        // ดึงข้อมูลใบเบิกตามสถานะ (สำหรับทุกหน่วยงาน)
        $stmt = $con->prepare("SELECT DISTINCT po_number, date, dept_id 
                               FROM po 
                               WHERE status = :status 
                               ORDER BY date DESC");
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if ($records) {
            echo '<table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">';
            echo '<thead class="bg-gray-100">';
            echo '<tr>';
            echo '<th class="py-2 px-4 border-b text-left font-medium text-gray-600">เลขที่ใบเบิก</th>';
            echo '<th class="py-2 px-4 border-b text-left font-medium text-gray-600">วันที่</th>';
            echo '<th class="py-2 px-4 border-b text-left font-medium text-gray-600">หน่วยงาน</th>';
            echo '<th class="py-2 px-4 border-b text-left font-medium text-gray-600">การดำเนินการ</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="divide-y divide-gray-200">';

            foreach ($records as $record) {
                echo '<tr class="hover:bg-gray-50">';
                echo '<td class="py-2 px-4 text-gray-700">';
                echo htmlspecialchars($record['po_number']) . ' ';
                echo '<a href="../generate_pdf.php?po_number=' . urlencode($record['po_number']) . '" target="_blank" class="text-blue-500 hover:underline ml-2">View</a>';
                echo '</td>';
                echo '<td class="py-2 px-4 text-gray-700">' . htmlspecialchars($record['date']) . '</td>';
                echo '<td class="py-2 px-4 text-gray-700">' . htmlspecialchars($record['dept_id']) . '</td>';
                echo '<td class="py-2 px-4 flex space-x-2">';

                // ปุ่มรออนุมัติ
                echo '<button onclick="markPending(\'' . htmlspecialchars($record['po_number']) . '\')" class="bg-yellow-500 text-white px-2 py-0.5 rounded-md text-xs hover:bg-yellow-600 transition">รออนุมัติ</button>';

                // แสดงปุ่มต่าง ๆ เฉพาะสถานะที่ไม่ใช่ 'ยกเลิก' และ 'อนุมัติ'
                if ($status !== '#' && $status !== '#') {
                    echo '<a href="../edit_po.php?po_number=' . urlencode($record['po_number']) . '" class="bg-blue-500 text-white px-2 py-0.5 rounded-md text-xs hover:bg-blue-600 transition">แก้ไข</a>';
                    echo '<button onclick="approvePo(\'' . htmlspecialchars($record['po_number']) . '\')" class="bg-green-500 text-white px-1 py-0.5 rounded-md text-xs hover:bg-green-600 transition">ยืนยันใบเบิก</button>';
                    echo '<button onclick="cancelPo(\'' . htmlspecialchars($record['po_number']) . '\')" class="bg-red-500 text-white px-2 py-0.5 rounded-md text-xs hover:bg-red-600 transition">ยกเลิกใบเบิก</button>';
                }

                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p class="text-gray-500 mt-4">ไม่พบใบเบิกในสถานะนี้</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-red-500 mt-4">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="text-red-500 mt-4">ไม่พบสถานะที่ระบุ</p>';
}
