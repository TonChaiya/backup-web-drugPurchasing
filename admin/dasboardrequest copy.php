<?php
session_start();
include('../config.php');

// ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// กำหนดค่าเริ่มต้นให้ตัวแปร $statusCounts
$statusCounts = [
    'Complete' => 0,
];

try {
    // ดึงข้อมูลจำนวนใบเบิกตามสถานะ (สำหรับทุกหน่วยงาน)
    $stmt = $con->prepare("SELECT status, COUNT(DISTINCT po_number) as count FROM processedgpo GROUP BY status");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // รวมข้อมูลที่ได้เข้ากับค่าเริ่มต้น
    foreach ($results as $row) {
        $statusCounts[$row['status']] = $row['count'];
    }
} catch (PDOException $e) {
    echo '<p class="text-red-500 text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <?php include('nav.php'); ?>

    <div class="container mx-auto mt-8">
        <h2 class="text-3xl font-bold mb-6 text-center">แดชบอร์ดผู้ดูแลระบบ</h2>
        <h3 class="text-3xl font-bold mb-6 text-center">รายงานใบเบิก GPO ที่จัดซื้อแล้ว</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($statusCounts as $status => $count): ?>
                <div class="p-4 border border-gray-300 rounded-lg shadow-md text-base cursor-pointer"
                    onclick="openModal('<?php echo htmlspecialchars($status); ?>')">
                    <h3 class="font-medium mb-2"><?php echo htmlspecialchars($status); ?></h3>
                    <p class="text-2xl font-semibold"><?php echo $count; ?> รายการ</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 hidden bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-semibold">รายละเอียดสถานะ</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <div id="modalContent" class="overflow-y-auto" style="max-height: 400px;">
                <!-- ข้อมูลใบเบิกจะแสดงที่นี่ -->
            </div>
        </div>
    </div>

    <script>
        function openModal(status) {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'ใบเบิกสถานะ: ' + status;

            fetch('../admin/getjavascript/adminget_po_by_status.php?status=' + encodeURIComponent(status))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('modalContent').innerHTML = '<p class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูล</p>';
                });
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        function approvePo(poNumber) {
            if (confirm('คุณต้องการยืนยันใบเบิกนี้ใช่หรือไม่?')) {
                fetch(`approve_po.php?po_number=${poNumber}`, {
                        method: 'GET'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('ใบเบิกได้รับการอนุมัติ');
                            location.reload();
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('เกิดข้อผิดพลาดในการยืนยันใบเบิก');
                    });
            }
        }

        function cancelPo(poNumber) {
            if (confirm("คุณต้องการยกเลิกใบเบิกนี้ใช่หรือไม่?")) {
                fetch('cancel_po.php', {
                        method: 'POST',
                        headers: {
                
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            po_number: poNumber,
                            status: 'ยกเลิกใบเบิก'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("ยกเลิกใบเบิกสำเร็จ");
                            location.reload();
                        } else {
                            alert("เกิดข้อผิดพลาด: " + data.message);
                        }
                    });
            }
        }

        function markPending(poNumber) {
            if (confirm("คุณต้องการเปลี่ยนสถานะใบเบิกนี้เป็น 'รออนุมัติ' หรือไม่?")) {
                fetch('markPending.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            po_number: poNumber
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("สถานะใบเบิกเปลี่ยนเป็น 'รออนุมัติ' สำเร็จ");
                            location.reload();
                        } else {
                            alert("เกิดข้อผิดพลาด: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("เกิดข้อผิดพลาดในการเปลี่ยนสถานะ");
                    });
            }
        }
    </script>
</body>

</html>