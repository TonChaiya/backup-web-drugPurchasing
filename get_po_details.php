<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['po_number'])) {
    $po_number = $_GET['po_number'];

    try {
        $stmt = $con->prepare("SELECT dept_id, working_code, item_code, format_item_code, quantity, price, remarks, packing_size, total_value, status 
                               FROM po 
                               WHERE po_number = :po_number 
                               ORDER BY date DESC");
        $stmt->bindParam(':po_number', $po_number);
        $stmt->execute();
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($details) {
            $dept_name = htmlspecialchars($details[0]['dept_id']);
            $total_items = count($details);
            $total_value = array_sum(array_column($details, 'total_value'));

            echo '<div class="box-border p-4 border border-gray-300 rounded-lg bg-white max-w-screen-lg w-full mx-auto">';
            
            echo '<div class="flex justify-between mb-4">';
            echo '<h2 class="text-lg font-bold">หน่วยงาน: ' . $dept_name . '</h2>';
            echo '</div>';

            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full border-collapse">';
            echo '<thead>';
            echo '<tr class="bg-gray-200">';
            echo '<th class="py-2 px-4 border-b text-left">ลำดับที่</th>';
            echo '<th class="py-2 px-4 border-b text-left">รหัสยา</th>';
            echo '<th class="py-2 px-4 border-b text-left">ชื่อยา</th>';
            echo '<th class="py-2 px-4 border-b text-left">รูปแบบ</th>';
            echo '<th class="py-2 px-4 border-b text-left">จำนวน</th>';
            echo '<th class="py-2 px-4 border-b text-left">ราคา</th>';
            echo '<th class="py-2 px-4 border-b text-left">หมายเหตุ</th>';
            echo '<th class="py-2 px-4 border-b text-left">ขนาดบรรจุ</th>';
            echo '<th class="py-2 px-4 border-b text-left">มูลค่า</th>';
            echo '<th class="py-2 px-4 border-b text-left">สถานะ</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($details as $index => $detail) {
                echo '<tr>';
                echo '<td class="py-2 px-4 border-b">' . ($index + 1) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['working_code']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['item_code']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['format_item_code']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['quantity']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['price']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['remarks']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['packing_size']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['total_value']) . '</td>';
                echo '<td class="py-2 px-4 border-b">' . htmlspecialchars($detail['status']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            echo '<div class="flex justify-between mt-4">';
            echo '<div><strong>จำนวนรวมรายการ:</strong> ' . $total_items . '</div>';
            echo '<div><strong>มูลค่ารวม:</strong> ' . number_format($total_value, 2) . ' บาท</div>';
            echo '</div>';
            
            echo '</div>';
        } else {
            echo '<p class="text-center text-red-500">ไม่พบข้อมูลรายละเอียดของใบเบิกนี้</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="text-center text-red-500">ไม่พบหมายเลขใบเบิก</p>';
}
?>
