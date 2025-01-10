<?php 
include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $hospital_name = $_SESSION['hospital_name']; // ดึง hospital_name จากเซสชัน

    try {
        // ดึงข้อมูลใบเบิกตามสถานะและหน่วยงาน โดยแสดงเฉพาะใบเบิกที่ตรงกับ hospital_name
        $stmt = $con->prepare("SELECT DISTINCT po_number, date 
                               FROM po 
                               WHERE status = :status AND dept_id = :hospital_name 
                               ORDER BY date DESC");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':hospital_name', $hospital_name);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if ($records) {
            echo '<table class="min-w-full bg-white border border-gray-300">';
            echo '<thead><tr><th class="py-2 px-4 border-b">เลขที่ใบเบิก</th><th class="py-2 px-4 border-b">วันที่</th><th class="py-2 px-4 border-b">การดำเนินการ</th></tr></thead>';
            echo '<tbody>';
            foreach ($records as $record) {
                echo '<tr>';
                echo '<td class="py-2 px-4 border-b">';
                echo htmlspecialchars($record['po_number']) . ' ';
                echo '<a href="generate_pdf.php?po_number=' . urlencode($record['po_number']) . '" target="_blank" class="text-blue-500 hover:underline ml-2">View</a>'; // ปุ่มดูใบเบิกในรูปแบบ PDF
                echo '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($record['date']) . '</td>';
                echo '<td class="py-2 px-4 border-b">';

                // แสดงปุ่มต่าง ๆ เฉพาะสถานะที่ไม่ใช่ 'ยกเลิก' และ 'อนุมัติ'
                if ($status !== 'ยกเลิกใบเบิก' && $status !== 'อนุมัติ') {
                    echo '<a href="edit_po.php?po_number=' . urlencode($record['po_number']) . '" class="text-blue-500 hover:underline mr-2">แก้ไข</a>';
                    echo '<button onclick="approvePo(\'' . htmlspecialchars($record['po_number']) . '\')" class="text-green-500 hover:underline mr-2">ยืนยันใบเบิก</button>';
                    echo '<button onclick="cancelPo(\'' . htmlspecialchars($record['po_number']) . '\')" class="text-red-500 hover:underline mr-2">ยกเลิกใบเบิก</button>';
                }

                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p class="text-gray-500">ไม่พบใบเบิกในสถานะนี้</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="text-red-500">ไม่พบสถานะที่ระบุ</p>';
}
?>
