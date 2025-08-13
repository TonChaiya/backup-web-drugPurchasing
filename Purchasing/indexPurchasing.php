<?php
ob_start(); // เริ่มเก็บข้อมูลใน buffer
session_start();
include('../config.php');
include_once '../admin/nav.php'; // เพิ่มส่วนของเมนูนำทาง

// ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// สร้าง CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    // ดึงข้อมูล status ที่ไม่ซ้ำจากตาราง po
    $query = "SELECT DISTINCT status FROM po";
    $stmt = $con->prepare($query);
    $stmt->execute();
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงจำนวนรายการในตาราง processed
    $processedQuery = "SELECT COUNT(*) as count FROM processed";
    $processedStmt = $con->prepare($processedQuery);
    $processedStmt->execute();
    $processedCount = $processedStmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

// ยกเลิกประมวลผล: ลบข้อมูลทั้งหมดใน processed
if (isset($_POST['cancel_processed'])) {
    try {
        $con->exec("DELETE FROM processed");
        echo '<div class="mt-2 text-red-600 text-center">ยกเลิกประมวลผลเรียบร้อยแล้ว (ลบข้อมูล processed ทั้งหมด)</div>';
        header("Refresh:0"); // รีเฟรชหน้าเว็บทันที
        exit;
    } catch (PDOException $e) {
        echo '<div class="mt-2 text-red-600 text-center">เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
ob_end_flush(); // ส่งข้อมูลทั้งหมดออกไปยังเบราว์เซอร์
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        .blinking {
            animation: blinkingText 1.5s infinite;
        }
        @keyframes blinkingText {
            0% { color: green; }
            50% { color: lightgreen; }
            100% { color: green; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="max-w-4xl mx-auto p-6">
        <!-- ฟอร์มสำหรับการเลือกสถานะและการประมวลผล -->
        <form action="process.php" method="post" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <!-- Select Status -->
            <div class="mb-6">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Select Status</label>
                <select id="status" name="status" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <option value="All">All</option>
                    <?php 
                    // แสดงรายการ status ที่ดึงมาจากฐานข้อมูล
                    foreach ($statuses as $row) {
                        echo '<option value="' . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    ?>
                </select>
            </div>

            <!-- Processed Entries -->
            <div class="mb-6">
                <p class="text-sm font-medium text-gray-700"><strong>Processed Entries:</strong> <span class="blinking"><?php echo $processedCount; ?></span></p>
            </div>

            <!-- Process Button -->
            <button type="submit" name="process" class="bg-blue-500 text-white w-full py-2 rounded-lg hover:bg-blue-600 mb-6">ประมวลผล</button>
        </form>

        <!-- ปุ่มยกเลิกประมวลผล -->
        <form method="post" class="mb-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" name="cancel_processed"
                class="bg-red-500 text-white w-full py-2 rounded-lg hover:bg-red-600"
                onclick="return confirm('คุณต้องการลบข้อมูลใน processed ทั้งหมด ใช่หรือไม่?');">
                ยกเลิกประมวลผล (ลบข้อมูล processed)
            </button>
        </form>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <button type="button" class="bg-green-500 text-white w-full py-2 rounded-lg hover:bg-green-600 mb-2" onclick="showReportOptions('GPO')">จัดซื้อ GPO</button>
            </div>

            <div>
                <button type="button" class="bg-yellow-500 text-white w-full py-2 rounded-lg hover:bg-yellow-600 mb-2" onclick="showReportOptions('จัดซื้อบริษัท')">จัดซื้อบริษัท</button>
            </div>

            <div>
                <button type="button" class="bg-gray-500 text-white w-full py-2 rounded-lg hover:bg-gray-600 mb-2" onclick="showReportOptions('บันทึกข้อความ')">บันทึกข้อความ</button>
            </div>
        </div>
    </div>

    <script>
        // ฟังก์ชันตรวจสอบฟอร์มก่อนส่ง
        function validateForm() {
            const status = document.getElementById('status').value;
            if (!status || status === '') {
                alert('กรุณาเลือกสถานะก่อนทำการประมวลผล');
                return false;
            }
            return true;
        }

        // ฟังก์ชันแสดงตัวเลือกรูปแบบรายงาน
        function showReportOptions(status) {
            Swal.fire({
                title: 'Select Report Format',
                text: `Generate report for ${status}`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'PDF',
                cancelButtonText: 'Word'
            }).then((result) => {
                if (result.isConfirmed) {
                    generateReport('pdf', status);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    generateReport('word', status);
                }
            });
        }

        // ฟังก์ชันสร้างรายงาน
        function generateReport(format, status) {
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to generate a ${format.toUpperCase()} report for ${status}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, generate it!',
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const encodedStatus = encodeURIComponent(status);
                    const url = 'generate_report.php?format=' + format + '&status=' + encodedStatus;
                    if (format === 'pdf') {
                        window.open(url, '_blank');
                    } else {
                        window.location.href = url;
                    }
                }
            });
        }
    </script>
</body>
</html>