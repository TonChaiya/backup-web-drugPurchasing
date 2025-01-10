<?php
session_start();
include('../config.php');

// ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
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

<body>

    <?php include('nav.php'); ?>
    <div class="container mx-auto mt-10">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center">เลือกประเภทของรายงาน</h2>
            <form method="POST" action="">
                <div class="space-y-4">
                    <!-- เลือกประเภทของรายงาน -->
                    <div>
                        <label class="block font-semibold">กรุณาเลือกประเภทของรายงาน:</label>
                        <div class="mt-2 space-y-2">
                            <div>
                                <input
                                    type="radio"
                                    id="report_all_items"
                                    name="report_type"
                                    value="all_items"
                                    required>
                                <label for="report_all_items" class="ml-2">รายงานการเบิกทั้งหมด (แยกรายการ)</label>
                            </div>
                            <div>
                                <input
                                    type="radio"
                                    id="report_combined_items"
                                    name="report_type"
                                    value="combined_items">
                                <label for="report_combined_items" class="ml-2">รายงานการเบิกทั้งหมด (รวมรายการ)</label>
                            </div>
                            <div>
                                <input
                                    type="radio"
                                    id="report_by_po"
                                    name="report_type"
                                    value="by_po">
                                <label for="report_by_po" class="ml-2">รายงานตามเลขที่ใบเบิก (ที่อนุมัติแล้วเท่านั้น)</label>
                                <input
                                    type="text"
                                    id="po_number"
                                    name="po_number"
                                    class="w-full mt-2 border border-gray-300 rounded-lg px-2 py-1"
                                    placeholder="กรอกเลขที่ใบเบิก"
                                    disabled>
                            </div>
                            <div>
                                <input
                                    type="radio"
                                    id="report_cancelled"
                                    name="report_type"
                                    value="cancelled">
                                <label for="report_cancelled" class="ml-2">รายงานที่ยกเลิกแล้ว</label>
                            </div>
                            <div>
                                <input
                                    type="radio"
                                    id="report_by_date"
                                    name="report_type"
                                    value="by_date">
                                <label for="report_by_date" class="ml-2">รายงานตามช่วงวันที่ (ที่อนุมัติแล้วเท่านั้น)</label>
                                <div class="flex mt-2 space-x-4">
                                    <input
                                        type="date"
                                        id="start_date"
                                        name="start_date"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                        placeholder="วันที่เริ่มต้น"
                                        disabled>
                                    <input
                                        type="date"
                                        id="end_date"
                                        name="end_date"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                        placeholder="วันที่สิ้นสุด"
                                        disabled>
                                </div>
                            </div>
                            <div class="mb-4">
                                <!-- Radio Button -->
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        id="report_by_medicine"
                                        name="report_type"
                                        value="by_medicine"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                                    <label for="report_by_medicine" class="text-sm font-medium text-gray-700">
                                        รายงานตามชื่อยา (ที่อนุมัติแล้วเท่านั้น)
                                    </label>
                                </div>

                                <!-- Input สำหรับค้นหายา -->
                                <div class="relative mt-3">
                                    <input
                                        type="text"
                                        id="medicine_name"
                                        name="medicine_name"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                                        placeholder="กรอกชื่อยา"
                                        oninput="searchMedicine()"
                                        disabled />
                                    <!-- Dropdown -->
                                    <div
                                        id="medicineDropdown"
                                        class="absolute left-0 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg hidden z-10 max-h-48 overflow-auto">
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">
                        ดูรายงาน
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ฟังก์ชันจัดการการเปิด/ปิดช่องกรอกข้อมูล
        function toggleInputs() {
            const reportType = document.querySelector('input[name="report_type"]:checked');

            // ตรวจสอบการเปิด/ปิดช่องกรอกข้อมูลอื่นๆ
            document.getElementById('po_number').disabled = reportType?.value !== 'by_po';
            document.getElementById('start_date').disabled = reportType?.value !== 'by_date';
            document.getElementById('end_date').disabled = reportType?.value !== 'by_date';
            document.getElementById('medicine_name').disabled = reportType?.value !== 'by_medicine';

            // หากเลือกตามชื่อยา ซ่อน/แสดง dropdown
            const dropdown = document.getElementById('medicineDropdown');
            if (reportType?.value !== 'by_medicine') {
                dropdown.classList.add('hidden');
            } else {
                dropdown.classList.remove('hidden');
            }
        }


        // ฟังก์ชันค้นหารายการยาในดรอปดาวน์
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

            // ส่งค่า working_code ไปใน URL ด้วย
            document.querySelector('form').addEventListener('submit', function(event) {
                event.preventDefault(); // หยุดการส่งฟอร์มแบบปกติ
                const reportType = document.querySelector('input[name="report_type"]:checked');

                if (reportType) {
                    // ตรวจสอบประเภทของรายงานที่เลือก
                    switch (reportType.value) {
                        case 'by_medicine':
                            const medicineName = document.getElementById('medicine_name').value.trim();
                            if (medicineName) {
                                window.location.href = `../admin/AdminReport/admin_medicine_report.php?working_code=${encodeURIComponent(selectedWorkingCode)}&medicine_name=${encodeURIComponent(medicineName)}`;
                            } else {
                                alert('กรุณากรอกชื่อยา');
                            }
                            break;
                            // ... ส่วนอื่นๆ ของฟังก์ชัน
                    }
                } else {
                    alert('กรุณาเลือกประเภทของรายงาน');
                }
            });
        }



        // ผูกฟังก์ชัน toggleInputs กับ radio buttons
        document.querySelectorAll('input[name="report_type"]').forEach(radio => {
            radio.addEventListener('change', toggleInputs);
        });

        // เรียกใช้ toggleInputs เพื่อให้แน่ใจว่าสถานะเริ่มต้นถูกตั้งค่า
        toggleInputs();
    </script>


    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            event.preventDefault(); // หยุดการส่งฟอร์มแบบปกติ
            const reportType = document.querySelector('input[name="report_type"]:checked');

            if (reportType) {
                // ตรวจสอบประเภทของรายงานที่เลือก
                switch (reportType.value) {
                    case 'all_items':
                        window.location.href = '../admin/AdminReport/all_purchases_report.php';
                        break;
                    case 'combined_items':
                        window.location.href = '../admin/AdminReport/all_combined_report.php';
                        break;
                    case 'by_po':
                        const poNumber = document.getElementById('po_number').value.trim();
                        if (poNumber) {
                            window.location.href = `../admin/AdminReport/admin_po_number_report.php?po_number=${encodeURIComponent(poNumber)}`;
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
                        if (medicineName) {
                            window.location.href = `../admin/AdminReport/admin_medicine_report.php?medicine_name=${encodeURIComponent(medicineName)}`;
                        } else {
                            alert('กรุณากรอกชื่อยา');
                        }
                        break;
                    case 'cancelled':
                        window.location.href = '../admin/AdminReport/admin_cancelled_report.php';
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