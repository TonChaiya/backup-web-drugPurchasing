<?php
// ------------------ Session & Config ------------------
session_start();
include('../config.php');

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ------------------ Query Data for Dropdowns ------------------
// Statuses from po
try {
    $stmt = $con->prepare("SELECT DISTINCT status FROM po ORDER BY status");
    $stmt->execute();
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $statuses = [];
    error_log("Error fetching statuses: " . $e->getMessage());
}
// Departments from po
$departments = [];
try {
    $stmt = $con->prepare("SELECT DISTINCT dept_id FROM po WHERE dept_id IS NOT NULL AND dept_id != '' ORDER BY dept_id");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $departments = [];
    error_log("Error fetching departments: " . $e->getMessage());
}
// ENUM status from processed
$processed_statuses = [];
try {
    $result = $con->query("SHOW COLUMNS FROM processed LIKE 'status'");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if ($row && preg_match("/enum\((.*)\)/", $row['Type'], $matches)) {
        $enums = str_getcsv($matches[1], ',', "'");
        $processed_statuses = array_map('trim', $enums);
    }
} catch (PDOException $e) {
    error_log("Error fetching processed statuses: " . $e->getMessage());
}

// ------------------ Create/Reset po_processed Button Logic ------------------
if (isset($_POST['create_po_processed'])) {
    try {
        // ลบตาราง po_processed ถ้ามีอยู่
        $con->exec("DROP TABLE IF EXISTS po_processed");
        // สร้างตารางใหม่จาก po
        $con->exec("CREATE TABLE po_processed AS SELECT * FROM po");
        // เพิ่มคอลัม purchase_status
        $con->exec("ALTER TABLE po_processed ADD purchase_status ENUM('GPO', 'จัดซื้อบริษัท')");
        echo '<div class="mt-2 text-green-600 text-right">สร้าง/รีเซ็ตตาราง po_processed สำเร็จแล้ว</div>';
    } catch (PDOException $e) {
        echo '<div class="mt-2 text-red-600 text-right">เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกประเภทของรายงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen">
    <?php include('nav.php'); ?>
    <div class="max-w-7xl mx-auto mt-8 px-4">
        <!-- Create/Reset po_processed Button (always visible) -->
        <form id="createPoProcessedForm" method="post" class="mb-4">
            <div class="flex justify-end">
                <button type="submit" name="create_po_processed" class="bg-yellow-500 text-white px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition"
                    onclick="return confirm('การสร้าง/รีเซ็ต po_processed จะลบข้อมูลเดิมทั้งหมดและสร้างใหม่จาก po คุณแน่ใจหรือไม่?');">
                    สร้าง/รีเซ็ต po_processed
                </button>
            </div>
        </form>
        <!-- Process Button (separate form, always visible) -->
        <form id="processForm" method="post" class="mb-6">
            <div class="flex justify-end">
                <button type="submit" name="process_update" class="bg-green-500 text-white px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 transition">ประมวลผลข้อมูล processed → po_processed</button>
            </div>
        </form>
        <?php
        // ------------------ Process Button Logic ------------------
        if (isset($_POST['process_update'])) {
            try {
                $stmt = $con->prepare("SELECT working_code, status, purchase_status FROM processed");
                $stmt->execute();
                $processedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $updateCount = 0;
                foreach ($processedRows as $row) {
                    $update = $con->prepare("UPDATE po_processed SET purchase_status = :purchase_status WHERE working_code = :working_code AND status = :status");
                    $update->execute([
                        ':purchase_status' => $row['purchase_status'],
                        ':working_code' => $row['working_code'],
                        ':status' => $row['status']
                    ]);
                    $updateCount += $update->rowCount();
                }
                echo '<div class="mt-2 text-green-600 text-right">อัปเดตข้อมูลเรียบร้อยแล้ว ' . $updateCount . ' รายการ</div>';
            } catch (PDOException $e) {
                echo '<div class="mt-2 text-red-600 text-right">เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        ?>
        <!-- Main Report Selection Card -->
        <div class="bg-white/80 backdrop-blur-xl p-8 md:p-10 rounded-2xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-center">เลือกประเภทของรายงาน</h2>
            <form method="POST" action="" class="main-report-form">
                <div class="space-y-4">
                    <div>
                        <div class="flex flex-col md:flex-row md:space-x-8">
                            <!-- Left: Main Reports -->
                            <div class="flex-1">
                                <label class="block font-semibold">กรุณาเลือกประเภทของรายงาน:</label>
                                <div class="mt-2 space-y-2">
                                    <!-- All Items (by item) -->
                                    <div>
                                        <input type="radio" id="report_all_items" name="report_type" value="all_items" required>
                                        <label for="report_all_items" class="ml-2">รายงานการเบิกทั้งหมด (แยกรายการ)</label>
                                        <div class="ml-6 mt-2 flex flex-col gap-2 w-64">
                                            <label class="block text-sm text-gray-600">เลือกหน่วยเบิก:</label>
                                            <select id="dept_all_items" name="dept_all_items" class="mt-1 border border-gray-300 rounded-lg px-2 py-1 w-full">
                                                <option value="all">ทุกหน่วยเบิก</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo htmlspecialchars($dept['dept_id']); ?>">
                                                        <?php echo htmlspecialchars($dept['dept_id']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label class="block text-sm text-gray-600 mt-2">เลือกสถานะ:</label>
                                            <select id="status_all_items" name="status_all_items" class="mt-1 border border-gray-300 rounded-lg px-2 py-1 w-full" disabled>
                                                <option value="all">ทุกสถานะ</option>
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo htmlspecialchars($status['status']); ?>">
                                                        <?php echo htmlspecialchars($status['status']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- All Items (combined) -->
                                    <div>
                                        <input type="radio" id="report_combined_items" name="report_type" value="combined_items">
                                        <label for="report_combined_items" class="ml-2">รายงานการเบิกทั้งหมด (รวมรายการ)</label>
                                        <div class="ml-6 mt-2">
                                            <label class="block text-sm text-gray-600">เลือกสถานะ:</label>
                                            <select id="status_combined_items" name="status_combined_items" class="mt-1 border border-gray-300 rounded-lg px-2 py-1 w-64" disabled>
                                                <option value="all">ทุกสถานะ</option>
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo htmlspecialchars($status['status']); ?>">
                                                        <?php echo htmlspecialchars($status['status']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- By PO -->
                                    <div>
                                        <input type="radio" id="report_by_po" name="report_type" value="by_po">
                                        <label for="report_by_po" class="ml-2">รายงานตามเลขที่ใบเบิก</label>
                                        <div class="ml-6 mt-2 space-y-2">
                                            <div>
                                                <label class="block text-sm text-gray-600">เลือกสถานะ:</label>
                                                <select id="status_by_po" name="status_by_po" class="mt-1 border border-gray-300 rounded-lg px-2 py-1 w-64" disabled>
                                                    <option value="all">ทุกสถานะ</option>
                                                    <?php foreach ($statuses as $status): ?>
                                                        <option value="<?php echo htmlspecialchars($status['status']); ?>">
                                                            <?php echo htmlspecialchars($status['status']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <input type="text" id="po_number" name="po_number" class="border border-gray-300 rounded-lg px-2 py-1 w-64" placeholder="กรอกเลขที่ใบเบิก" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Cancelled -->
                                    <div>
                                        <input type="radio" id="report_cancelled" name="report_type" value="cancelled">
                                        <label for="report_cancelled" class="ml-2">รายงานที่ยกเลิกแล้ว</label>
                                    </div>
                                    <!-- By Date -->
                                    <div>
                                        <input type="radio" id="report_by_date" name="report_type" value="by_date">
                                        <label for="report_by_date" class="ml-2">รายงานตามช่วงวันที่ (ที่อนุมัติแล้วเท่านั้น)</label>
                                        <div class="flex mt-2 space-x-4">
                                            <input type="date" id="start_date" name="start_date" class="border border-gray-300 rounded-lg px-2 py-1 w-64" placeholder="วันที่เริ่มต้น" disabled>
                                            <input type="date" id="end_date" name="end_date" class="border border-gray-300 rounded-lg px-2 py-1 w-64" placeholder="วันที่สิ้นสุด" disabled>
                                        </div>
                                    </div>
                                    <!-- By Medicine -->
                                    <div class="mb-4">
                                        <div class="flex items-center space-x-2">
                                            <input type="radio" id="report_by_medicine" name="report_type" value="by_medicine" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                                            <label for="report_by_medicine" class="text-sm font-medium text-gray-700">รายงานตามชื่อยา (ที่อนุมัติแล้วเท่านั้น)</label>
                                        </div>
                                        <div class="relative mt-3">
                                            <input type="text" id="medicine_name" name="medicine_name" class="border border-gray-300 rounded-lg px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 w-64" placeholder="กรอกชื่อยา" oninput="searchMedicine()" disabled />
                                            <div id="medicineDropdown" class="absolute left-0 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg hidden z-10 max-h-48 overflow-auto"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Right: Processed/po_processed Reports -->
                            <div class="flex-1 mt-8 md:mt-0">
                                <!-- Processed -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                                    <input type="radio" id="report_processed" name="report_type" value="processed">
                                    <label for="report_processed" class="ml-2">รายงาน หลังกดประมวลผล (จากตาราง processed ที่เเยกบริษัทเเล้วเท่านั้น)</label>
                                    <div class="ml-6 mt-2">
                                        <label class="block text-sm text-gray-600">เลือกสถานะ (processed):</label>
                                        <div id="status_processed_group" class="flex flex-wrap gap-2" style="opacity:0.5; pointer-events:none;">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" value="all" id="status_processed_all" name="status_processed[]" class="form-checkbox"> <span class="ml-1">ทุกสถานะ</span>
                                            </label>
                                            <?php foreach ($processed_statuses as $status): ?>
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" value="<?php echo htmlspecialchars($status); ?>" name="status_processed[]" class="form-checkbox"> <span class="ml-1"><?php echo htmlspecialchars($status); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- po_processed (NEW) -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <input type="radio" id="report_po_processed" name="report_type" value="po_processed">
                                    <label for="report_po_processed" class="ml-2">รายงานข้อมูล po_processed (กรองตามสถานะ)</label>
                                    <div class="ml-6 mt-2">
                                        <label class="block text-sm text-gray-600">เลือกสถานะ (po_processed):</label>
                                        <select id="status_po_processed" name="status_po_processed" class="mt-1 border border-gray-300 rounded-lg px-2 py-1 w-64" disabled>
                                            <option value="all">ทุกสถานะ</option>
                                            <option value="อนุมัติ">อนุมัติ</option>
                                            <option value="รออนุมัติ">รออนุมัติ</option>
                                            <option value="ยกเลิกใบเบิก">ยกเลิกใบเบิก</option>
                                            <option value="Complete">Complete</option>
                                            <option value="ไตรมาสที่ 1 Complete">ไตรมาสที่ 1 Complete</option>
                                            <option value="ไตรมาสที่ 2 Complete">ไตรมาสที่ 2 Complete</option>
                                        </select>
                                    </div>
                                    <div class="ml-6 mt-2">
                                        <label class="block text-sm text-gray-600">เลือก purchase_status:</label>
                                        <select id="purchase_status_po_processed" name="purchase_status_po_processed" class="mt-1 border border-gray-300 rounded-lg px-2 py-1 w-64" disabled>
                                            <option value="all">ทุกประเภท</option>
                                            <option value="GPO">GPO</option>
                                            <option value="จัดซื้อบริษัท">จัดซื้อบริษัท</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl shadow-sm hover:shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">ดูรายงาน</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ------------------ JS: Toggle Inputs, Search, Redirect ------------------ -->
    <script>
        // Toggle enable/disable inputs by report type
        function toggleInputs() {
            const reportType = document.querySelector('input[name="report_type"]:checked');
            document.getElementById('po_number').disabled = reportType?.value !== 'by_po';
            document.getElementById('start_date').disabled = reportType?.value !== 'by_date';
            document.getElementById('end_date').disabled = reportType?.value !== 'by_date';
            document.getElementById('medicine_name').disabled = reportType?.value !== 'by_medicine';
            document.getElementById('status_all_items').disabled = reportType?.value !== 'all_items';
            document.getElementById('status_combined_items').disabled = reportType?.value !== 'combined_items';
            document.getElementById('status_by_po').disabled = reportType?.value !== 'by_po';
            // processed (checkbox group)
            const processedGroup = document.getElementById('status_processed_group');
            if (reportType?.value === 'processed') {
                processedGroup.style.opacity = '1';
                processedGroup.style.pointerEvents = 'auto';
            } else {
                processedGroup.style.opacity = '0.5';
                processedGroup.style.pointerEvents = 'none';
                processedGroup.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
            }
            // po_processed
            document.getElementById('status_po_processed').disabled = reportType?.value !== 'po_processed';
            document.getElementById('purchase_status_po_processed').disabled = reportType?.value !== 'po_processed';
            // medicine dropdown
            const dropdown = document.getElementById('medicineDropdown');
            if (reportType?.value !== 'by_medicine') {
                dropdown.classList.add('hidden');
            } else {
                dropdown.classList.remove('hidden');
            }
            // dept dropdown
            document.getElementById('dept_all_items').disabled = reportType?.value !== 'all_items';
        }
        // Search medicine dropdown
        let selectedWorkingCode = null;
        function searchMedicine() {
            const query = document.getElementById("medicine_name").value.trim();
            const dropdown = document.getElementById("medicineDropdown");
            if (query.length > 0) {
                fetch(`../report/search_drug_list.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = "";
                        if (Array.isArray(data) && data.length > 0) {
                            dropdown.classList.remove("hidden");
                            data.forEach(item => {
                                const option = document.createElement("div");
                                option.classList.add("px-4", "py-2", "text-sm", "hover:bg-gray-200", "cursor-pointer");
                                option.textContent = item.name_item_code;
                                option.onclick = () => selectMedicine(item.working_code, item.name_item_code);
                                dropdown.appendChild(option);
                            });
                        } else {
                            dropdown.classList.add("hidden");
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching medicine list:", error);
                    });
            } else {
                dropdown.classList.add("hidden");
            }
        }
        function selectMedicine(workingCode, medicineName) {
            selectedWorkingCode = workingCode;
            const input = document.getElementById("medicine_name");
            input.value = medicineName;
            const dropdown = document.getElementById("medicineDropdown");
            dropdown.classList.add("hidden");
        }
        // Bind toggleInputs to radio buttons
        document.querySelectorAll('input[name="report_type"]').forEach(radio => {
            radio.addEventListener('change', toggleInputs);
        });
        toggleInputs(); // initial
    </script>
    <script>
        // Main report form submit: redirect by report type
        document.querySelector('.main-report-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const reportType = document.querySelector('input[name="report_type"]:checked');
            if (reportType) {
                switch (reportType.value) {
                    case 'all_items':
                        const statusAllItems = document.getElementById('status_all_items').value;
                        const deptAllItems = document.getElementById('dept_all_items').value;
                        window.location.href = `../admin/AdminReport/all_purchases_report.php?status=${encodeURIComponent(statusAllItems)}&dept_id=${encodeURIComponent(deptAllItems)}`;
                        break;
                    case 'combined_items':
                        const statusCombinedItems = document.getElementById('status_combined_items').value;
                        window.location.href = `../admin/AdminReport/all_combined_report.php?status=${encodeURIComponent(statusCombinedItems)}`;
                        break;
                    case 'by_po':
                        const poNumber = document.getElementById('po_number').value.trim();
                        const statusByPo = document.getElementById('status_by_po').value;
                        if (poNumber) {
                            window.location.href = `../admin/AdminReport/admin_po_number_report.php?po_number=${encodeURIComponent(poNumber)}&status=${encodeURIComponent(statusByPo)}`;
                        } else {
                            alert('กรุณากรอกเลขที่ใบเบิก');
                        }
                        break;
                    case 'by_date':
                        const startDate = document.getElementById('start_date').value;
                        const endDate = document.getElementById('end_date').value;
                        if (startDate && endDate) {
                            window.location.href = `../admin/AdminReport/admin_date_range_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                        } else {
                            alert('กรุณาเลือกช่วงวันที่');
                        }
                        break;
                    case 'by_medicine':
                        const medicineName = document.getElementById('medicine_name').value.trim();
                        if (medicineName && selectedWorkingCode) {
                            window.location.href = `../admin/AdminReport/admin_medicine_report.php?working_code=${encodeURIComponent(selectedWorkingCode)}&medicine_name=${encodeURIComponent(medicineName)}`;
                        } else {
                            alert('กรุณาเลือกชื่อยาจากรายการ');
                        }
                        break;
                    case 'cancelled':
                        window.location.href = '../admin/AdminReport/admin_cancelled_report.php';
                        break;
                    case 'processed':
                        const processedCheckboxes = document.querySelectorAll('#status_processed_group input[type=checkbox]:checked');
                        let selectedStatuses = Array.from(processedCheckboxes).map(cb => cb.value);
                        if (selectedStatuses.length === 0 || selectedStatuses.includes('all')) {
                            selectedStatuses = ['all'];
                        }
                        window.location.href = `../admin/AdminReport/processed_report.php?status=${encodeURIComponent(selectedStatuses.join(','))}`;
                        break;
                    case 'po_processed':
                        const statusPoProcessed = document.getElementById('status_po_processed').value;
                        const purchaseStatusPoProcessed = document.getElementById('purchase_status_po_processed').value;
                        window.location.href = `../admin/AdminReport/processed_po_report.php?status=${encodeURIComponent(statusPoProcessed)}&purchase_status=${encodeURIComponent(purchaseStatusPoProcessed)}`;
                        break;
                    default:
                        alert('กรุณาเลือกประเภทของรายงาน');
                }
            } else {
                alert('กรุณาเลือกประเภทของรายงาน');
            }
        });
    </script>
</body>
</html>