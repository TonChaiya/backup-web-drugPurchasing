<?php
session_start();
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if `hospital_name` is set in the session
if (!isset($_SESSION['hospital_name'])) {
    // Fetch `hospital_name` from the database
    $stmt = $con->prepare("SELECT hospital_name FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['hospital_name'] = $user['hospital_name'];
    } else {
        echo '<p class="text-center text-red-500">ไม่พบข้อมูลหน่วยเบิกในเซสชัน</p>';
        exit;
    }
}

$hospital_name = $_SESSION['hospital_name'];
$withdrawRecords = [];

try {
    // Fetch withdrawal records specific to the user's department
    $stmt = $con->prepare("
        SELECT po_number, MAX(date) as date 
        FROM po 
        WHERE dept_id = :hospital_name 
        GROUP BY po_number 
        ORDER BY MAX(date) DESC
    ");
    $stmt->bindParam(':hospital_name', $hospital_name);
    $stmt->execute();
    $withdrawRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                    <button type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="absolute -inset-0.5"></span>
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex flex-shrink-0 items-center">
                        <img class="h-8 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company">
                    </div>
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <a href="dashboard.php" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white" aria-current="page">Dashboard</a>
                            <a href="page-report.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">รายงาน</a>
                            <a href="dashboard-copy.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
                            <a href="register.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">register</a>
                        </div>
                    </div>
                </div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <button type="button" class="relative rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">View notifications</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>

                    <div class="relative ml-3">
                        <div>
                            <button type="button" class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="absolute -inset-1.5"></span>
                                <span class="sr-only">Open user menu</span>
                                <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            </button>
                        </div>

                        <div id="dropdown-menu" class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Your Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Settings</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:hidden" id="mobile-menu">
            <div class="space-y-1 px-2 pb-3 pt-2">
                <a href="dashboard.php" class="block rounded-md bg-gray-900 px-3 py-2 text-base font-medium text-white" aria-current="page">Dashboard</a>
                <a href="page-report.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">รายงาน</a>
                <a href="dashboard-copy.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
                <a href="register.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">register</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 flex space-x-6">
        <!-- คอนเทนเนอร์ด้านซ้าย (แดชบอร์ดเดิม) -->
        <div class="w-1/2 bg-gray-50 shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">แดชบอร์ดใบเบิกของหน่วยงาน</h2>
            <div class="bg-white shadow-md rounded-lg p-6 overflow-y-auto max-h-96">
                <?php if (!empty($withdrawRecords)): ?>
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="py-3 px-6 border-b text-left text-sm text-gray-600">เลขที่ใบเบิก</th>
                                <th class="py-3 px-6 border-b text-left text-sm text-gray-600">วันที่</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($withdrawRecords as $record): ?>
                                <tr class="hover:bg-gray-100 cursor-pointer" onclick="openModal('<?php echo htmlspecialchars($record['po_number']); ?>')">
                                    <td class="py-3 px-6 border-b text-sm"><?php echo htmlspecialchars($record['po_number']); ?></td>
                                    <td class="py-3 px-6 border-b text-sm"><?php echo htmlspecialchars($record['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลใบเบิก</p>
                <?php endif; ?>
            </div>
        </div>


        <!-- คอนเทนเนอร์ด้านขวา (ตัวเลือกการรายงาน) -->
        <div class="w-1/2 bg-gray-50 shadow-lg rounded-lg p-6 space-y-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">เลือกประเภทการรายงาน</h3>

            <form class="space-y-4">
                <!-- รายงานจัดซื้อทั้งหมด -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600" id="allPurchases" onclick="toggleInputs()">
                    <label for="allPurchases" class="text-sm text-gray-700">รายงานจัดซื้อทั้งหมด</label>
                </div>

                <!-- รายงานตามเลขที่ใบเบิก -->
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600" id="poNumberReport" onclick="toggleInputs()">
                        <label for="poNumberReport" class="text-sm text-gray-700">รายงานตามเลขที่ใบเบิก (ที่อนุมัติแล้วเท่านั้น)</label>
                    </div>
                    <input type="text" class="form-input block w-full mt-1 p-2 border border-gray-300 rounded text-sm" id="poNumberInput" placeholder="กรอกเลขที่ใบเบิก" disabled>
                </div>

                <!-- รายงานที่ยกเลิกแล้ว -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600" id="cancelledReports" onclick="toggleInputs()">
                    <label for="cancelledReports" class="text-sm text-gray-700">รายงานที่ยกเลิกแล้ว</label>
                </div>

                <!-- รายงานตามช่วงวันที่ -->
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600" id="dateRangeReport" onclick="toggleInputs()">
                        <label for="dateRangeReport" class="text-sm text-gray-700">รายงานตามช่วงวันที่</label>
                    </div>
                    <div class="flex space-x-2">
                        <input type="date" class="form-input p-2 border border-gray-300 rounded w-1/2 text-sm" id="startDate" disabled>
                        <input type="date" class="form-input p-2 border border-gray-300 rounded w-1/2 text-sm" id="endDate" disabled>
                    </div>
                </div>

                <!-- รายงานตามชื่อยา -->
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600" id="medicineNameReport" onclick="toggleInputs()">
                        <label for="medicineNameReport" class="text-sm text-gray-700">รายงานตามชื่อยา</label>
                    </div>
                    <div class="relative">
                        <input type="text" class="form-input p-2 border border-gray-300 rounded w-full text-sm" id="medicineNameInput" placeholder="กรอกชื่อยา" oninput="searchMedicine()">
                        <div id="medicineDropdown" class="absolute bg-white border border-gray-300 rounded shadow-lg mt-1 max-h-40 w-full overflow-y-auto hidden">
                            <!-- รายการดรอปดาวน์ของชื่อยาจะถูกสร้างที่นี่ -->
                        </div>
                    </div>
                </div>

                <!-- ปุ่ม Generate Report -->
                <button type="button" onclick="generateReport()" id="generateReportBtn" class="w-full bg-indigo-500 text-white font-semibold py-2 px-4 rounded hover:bg-indigo-600 mt-4">
                    Generate Report
                </button>
            </form>


            <div id="modal" class="fixed inset-0 hidden bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 box-border">
                <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">รายละเอียดใบเบิก</h3>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                    </div>
                    <div id="modalContent" class="overflow-y-auto" style="max-height: 400px;">
                    </div>
                </div>
            </div>

            <script>
                function openModal(poNumber) {
                    document.getElementById('modal').classList.remove('hidden');
                    fetch('get_po_details.php?po_number=' + encodeURIComponent(poNumber))
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
            </script>

            <!-- JavaScript -->
            <script>
                // เปิดใช้งานฟิลด์วันที่เมื่อเลือกช่วงวันที่
                document.getElementById("dateRangeReport").addEventListener("change", function() {
                    const isEnabled = this.checked;
                    document.getElementById("startDate").disabled = !isEnabled;
                    document.getElementById("endDate").disabled = !isEnabled;
                });

                // ตรวจสอบให้แน่ใจว่าเลือกเช็คบ็อกซ์ใดเช็คบ็อกซ์หนึ่ง
                function generateReport() {
                    const allPurchases = document.getElementById("allPurchases").checked;
                    const poNumberReport = document.getElementById("poNumberReport").checked;
                    const cancelledReports = document.getElementById("cancelledReports").checked;
                    const dateRangeReport = document.getElementById("dateRangeReport").checked;
                    const medicineNameReport = document.getElementById("medicineNameReport").checked;

                    const poNumber = document.getElementById("poNumberInput").value.trim();
                    const startDate = document.getElementById("startDate").value;
                    const endDate = document.getElementById("endDate").value;

                    // ตรวจสอบว่าเลือกเช็คบ็อกซ์ใดเช็คบ็อกซ์หนึ่ง
                    if (allPurchases) {
                        window.location.href = 'report/all_purchases_report.php';
                    } else if (poNumberReport && poNumber) {
                        window.location.href = 'report/po_number_report.php?po_number=' + encodeURIComponent(poNumber);
                    } else if (cancelledReports) {
                        window.location.href = 'report/cancelled_report.php';
                    } else if (dateRangeReport && startDate && endDate) {
                        window.location.href = 'report/date_range_report.php?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
                    } else if (medicineNameReport && selectedWorkingCode) {
                        window.location.href = `report/medicine_report.php?working_code=${encodeURIComponent(selectedWorkingCode)}`;
                    } else {
                        alert("กรุณาเลือกตัวเลือกและกรอกข้อมูลที่จำเป็นก่อนกด Generate Report");
                    }
                }

                // ฟังก์ชันค้นหารายการยาในดรอปดาวน์
                let selectedWorkingCode = null;

                function searchMedicine() {
                    const query = document.getElementById("medicineNameInput").value;
                    if (query.length > 0) {
                        fetch(`report/search_drug_list.php?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                const dropdown = document.getElementById("medicineDropdown");
                                dropdown.innerHTML = "";

                                if (Array.isArray(data) && data.length > 0) {
                                    dropdown.classList.remove("hidden");
                                    data.forEach(item => {
                                        const option = document.createElement("div");
                                        option.classList.add("p-2", "hover:bg-gray-200", "cursor-pointer");
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
                        document.getElementById("medicineDropdown").classList.add("hidden");
                    }
                }

                // ฟังก์ชันเลือกยาและเก็บค่า working_code
                function selectMedicine(workingCode, name) {
                    selectedWorkingCode = workingCode;
                    document.getElementById("medicineNameInput").value = name; // แสดงชื่อยาใน input
                    document.getElementById("medicineDropdown").classList.add("hidden");
                }

                // โค้ดสำหรับบังคับให้เลือกได้เพียงเช็คบ็อกซ์เดียว
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                if (cb !== this) cb.checked = false;
                            });
                        }
                    });
                });

                function toggleInputs() {
                    const allPurchases = document.getElementById("allPurchases").checked;
                    const poNumberReport = document.getElementById("poNumberReport").checked;
                    const cancelledReports = document.getElementById("cancelledReports").checked;
                    const dateRangeReport = document.getElementById("dateRangeReport").checked;
                    const medicineNameReport = document.getElementById("medicineNameReport").checked;

                    // เปิด/ปิดการใช้งานช่องกรอกตามสถานะของเช็คบ็อกซ์
                    document.getElementById("poNumberInput").disabled = !poNumberReport;
                    document.getElementById("startDate").disabled = !dateRangeReport;
                    document.getElementById("endDate").disabled = !dateRangeReport;
                    document.getElementById("medicineNameInput").disabled = !medicineNameReport;
                }

                // เรียกใช้ toggleInputs เมื่อมีการเปลี่ยนสถานะของเช็คบ็อกซ์ใด ๆ
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('click', function() {
                        toggleInputs();
                        // บังคับให้เลือกเช็คบ็อกซ์ได้เพียงตัวเดียว
                        if (this.checked) {
                            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                if (cb !== this) cb.checked = false;
                            });
                        }
                    });
                });
            </script>



            <!-- Script to toggle dropdown menu เมนู profile-->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const profileButton = document.getElementById('user-menu-button');
                    const dropdownMenu = document.getElementById('dropdown-menu');

                    // Toggle dropdown menu on profile button click
                    profileButton.addEventListener('click', function(event) {
                        event.preventDefault();
                        dropdownMenu.classList.toggle('hidden');
                    });

                    // Close dropdown menu on outside click
                    document.addEventListener('click', function(event) {
                        if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                            dropdownMenu.classList.add('hidden');
                        }
                    });
                });
            </script>
</body>

</html>