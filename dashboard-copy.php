<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// เรียกใช้การเชื่อมต่อฐานข้อมูลจาก config.php
require_once 'config.php';

// ดึง user_id จากเซสชัน
$user_id = $_SESSION['user_id'];

// ดึง hospital_name ของผู้ใช้ที่ล็อกอินอยู่โดยใช้ PDO
try {
    $stmt = $con->prepare("SELECT hospital_name FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $hospital_name = $stmt->fetchColumn();

    // ตรวจสอบว่าพบ hospital_name หรือไม่
    $hospital_name = $hospital_name ? htmlspecialchars($hospital_name, ENT_QUOTES, 'UTF-8') : 'ไม่พบข้อมูล';

} catch (PDOException $e) {
    error_log("Failed to fetch hospital_name: " . $e->getMessage(), 0);
    $hospital_name = 'เกิดข้อผิดพลาด';
}


// ใช้ username จากเซสชันที่เก็บไว้ตอนล็อกอิน และป้องกัน XSS ด้วย htmlspecialchars
$username = isset($_SESSION['username_account']) ? htmlspecialchars($_SESSION['username_account'], ENT_QUOTES, 'UTF-8') : 'ไม่พบชื่อผู้ใช้';


// ฟังก์ชันสำหรับสร้างหมายเลขใบเบิกใหม่
function generateWithdrawNumber() {
    $latestNumber = 'A01'; // สมมติหมายเลขใบเบิกล่าสุด
    return $latestNumber;
}

// กำหนดหมายเลขใบเบิก
$withdrawNumber = generateWithdrawNumber();

// ตั้งค่าวันที่ปัจจุบันในรูปแบบ `วัน/เดือน/ปี พ.ศ.`
date_default_timezone_set('Asia/Bangkok');
$currentDate = date('d/m/') . (date('Y') + 543);
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
    <div class="flex h-16 items-center justify-between">
      <div class="flex items-center">
        <button id="mobile-menu-button" class="text-gray-400 hover:bg-gray-700 hover:text-white sm:hidden focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
          <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
          </svg>
        </button>
        <div class="flex-shrink-0">
          <img class="h-8 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company">
        </div>
        <div class="hidden sm:flex sm:ml-6">
          <a href="dashboard.php" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white">Dashboard</a>
          <a href="page-report.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">รายงาน</a>
          <a href="dashboard-copy.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
          <a href="register.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">register</a>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <button type="button" class="relative bg-gray-800 p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
          <span class="sr-only">View notifications</span>
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.82 12.82a4 4 0 10-5.64 0 4 4 0 015.64 0zM3 13h1m3 0h1m3 0h1M8 17h1m2 0h1m2 0h1m2 0h1m2 0h1m2 0h1M12 17v3m0-3h1m0 3v1" />
          </svg>
        </button>
        <!-- Profile dropdown -->
        <div class="relative">
          <button type="button" class="flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button">
            <span class="sr-only">Open user menu</span>
            <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
          </button>
          <div id="dropdown-menu" class="hidden absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700">Your Profile</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700">Settings</a>
            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700">Sign out</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Mobile menu, hidden initially -->
  <div id="mobile-menu" class="hidden sm:hidden">
    <div class="space-y-1 px-2 pb-3 pt-2">
      <a href="dashboard.php" class="block rounded-md bg-gray-900 px-3 py-2 text-base font-medium text-white">Dashboard</a>
      <a href="page-report.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">รายงาน</a>
      <a href="dashboard-copy.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
      <a href="register.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">register</a>
    </div>
  </div>
</nav>

<!-- Form Section -->
<div class="container mx-auto p-8 bg-white rounded-lg shadow-md mt-6">
    <h2 class="text-lg font-bold mb-4">กรอกข้อมูลเบิก</h2>
    <form action="submit_po_items.php" method="POST">
        <!-- ข้อมูลส่วนที่หนึ่ง -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700">เลขที่ใบเบิก:</label>
                <input type="text" name="withdraw_number" value="<?php echo $withdrawNumber; ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
            </div>
            <div>
                <label class="block text-gray-700">วันที่และเวลา:</label>
                <input type="text" name="withdraw_date" value="<?php echo $currentDate; ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
            </div>
            <div>
                <label class="block text-gray-700">หน่วยงาน:</label>
                <input type="text" name="department" value="<?php echo $hospital_name; ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-md" readonly>
            </div>
        </div>

        
        <!-- ข้อมูลส่วนที่สองในแถวเดียวกัน -->
        <div class="grid grid-cols-6 gap-4 mb-4">
            <!-- HTML Code -->
<div class="col-span-1">
    <label class="block text-gray-700">รหัสสินค้า:</label>
    <input type="text" id="product_code" name="product_code" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
</div>

            <div class="relative">
              <label class="block text-gray-700">ชื่อสินค้า:</label>
              <input type="text" id="product_name" name="product_name" 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" 
                    onkeyup="fetchSuggestions()" autocomplete="off">
              
              <!-- แสดงรายการค้นหา -->
              <div id="suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden" style="max-height: 200px; overflow-y: auto;"></div>

            </div>
            <div class="col-span-1">
                <label class="block text-gray-700">รูปแบบสินค้า:</label>
                <input type="text" id="product_format" name="product_format" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-gray-700">ขนาดบรรจุ:</label>
                <input type="text" id="packing_size" name="packing_size" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-gray-700">จำนวน:</label>
                <input type="number" name="quantity" id="quantity" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border">
            </div>
            <div class="col-span-1">
                <label class="block text-gray-700">ราคา:</label>
                <input type="text" id="price" name="price" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
            </div>
        </div>

        <!-- ข้อมูลส่วนที่สาม -->
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="block text-gray-700">มูลค่า:</label>
                <input type="text" name="total_value" id="total_value" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border" readonly>
            </div>
            <div>
                <label class="block text-gray-700">หมายเหตุ:</label>
                <textarea id="remarks" name="remarks" class="w-full mt-1 p-2 border border-gray-300 rounded-md box-border"></textarea>
            </div>
        </div>

            <input type="hidden" name="po_number" value="A01">
            <input type="hidden" name="date" value="01/11/2566"> <!-- ตัวอย่าง -->
            <input type="hidden" name="dept_id" value="admin/CHAYA">
            <div class="mt-6">
                <button type="button" onclick="addItemToDashboard()" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Add Item</button>
                <button type="button" onclick="saveDashboardData()" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">บันทึกข้อมูล</button>

            </div>
    </form>
</div>

<!-- Dashboard Section -->
<div class="container mx-auto p-8 bg-white rounded-lg shadow-md mt-6 border border-gray-300 box-border">
    <h2 class="text-lg font-bold mb-4">แดชบอร์ด</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">รหัสยา</th>
                    <th class="py-2 px-4 border-b">รายการยา</th>
                    <th class="py-2 px-4 border-b">รูปแบบ</th>
                    <th class="py-2 px-4 border-b">ขนาดบรรจุ</th>
                    <th class="py-2 px-4 border-b">จำนวนเบิก</th>
                    <th class="py-2 px-4 border-b">ราคา</th>
                    <th class="py-2 px-4 border-b">มูลค่า</th>
                    <th class="py-2 px-4 border-b">หมายเหตุ</th>
                    <th class="py-2 px-4 border-b">ลบ</th>
                </tr>
            </thead>
            <tbody>
                <!-- รายการจะเพิ่มที่นี่ -->
            </tbody>
        </table>
    </div>
</div>



<!-- Script to toggle dropdown menu -->
<script>
     document.addEventListener('DOMContentLoaded', function() {
        const profileButton = document.getElementById('user-menu-button');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        // Toggle dropdown menu on profile button click
        profileButton.addEventListener('click', function(event) {
            event.preventDefault();
            dropdownMenu.classList.toggle('hidden');
        });

        // Toggle mobile menu on button click
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.style.display = mobileMenu.style.display === "none" ? "block" : "none";
        });

        // Close dropdown menu on outside click
        document.addEventListener('click', function(event) {
            if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        // Add input event listeners for total value calculation
        document.getElementById('price').addEventListener('input', calculateTotalValue);
        document.getElementById('quantity').addEventListener('input', calculateTotalValue);
    });

    function fetchSuggestions() {
        const input = document.getElementById('product_name').value;
        const suggestionsDiv = document.getElementById('suggestions');

        if (input.length < 2) {
            suggestionsDiv.innerHTML = ''; 
            suggestionsDiv.classList.add('hidden');
            return;
        }

        fetch(`search_items.php?query=${input}`)
            .then(response => response.json())
            .then(data => {
                let suggestions = '';
                data.forEach(item => {
                    suggestions += `<div class="px-4 py-2 cursor-pointer hover:bg-gray-200" onclick='selectItem(${JSON.stringify(item)})'>${item.name_item_code}</div>`;
                });
                suggestionsDiv.innerHTML = suggestions;
                suggestionsDiv.classList.remove('hidden');
            });
    }

    function selectItem(item) {
        const productNameInput = document.getElementById('product_name');
        const productCodeInput = document.getElementById('product_code');
        const productFormatInput = document.getElementById('product_format');
        const packingSizeInput = document.getElementById('packing_size');
        const priceInput = document.getElementById('price');
        const suggestionsDiv = document.getElementById('suggestions');

        // Validate if elements exist
        if (productNameInput && productCodeInput && productFormatInput && packingSizeInput && priceInput) {
            productNameInput.value = item.name_item_code;
            productCodeInput.value = item.working_code;
            productFormatInput.value = item.format_item;
            packingSizeInput.value = item.packing_code;
            priceInput.value = item.price_unit_code;
        }

        // Hide suggestions after selection
        if (suggestionsDiv) {
            suggestionsDiv.classList.add('hidden');
        }
    }

    // Prevent form submission with Enter key
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        if (form) {
            form.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        }
    });

    // Calculate total value
    function calculateTotalValue() {
        const quantityInput = document.getElementById('quantity');
        const priceInput = document.getElementById('price');
        const totalValueInput = document.getElementById('total_value');

        if (quantityInput && priceInput && totalValueInput) {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            totalValueInput.value = quantity > 0 && price > 0 ? (quantity * price).toFixed(2) : ''; // Show 2 decimals
        }
    }


    function addItemToDashboard() {
        const productCode = document.getElementById('product_code').value;
        const productName = document.getElementById('product_name').value;
        const productFormat = document.getElementById('product_format').value;
        const packingSize = document.getElementById('packing_size').value;
        const quantity = document.getElementById('quantity').value;
        const price = document.getElementById('price').value;
        const totalValue = document.getElementById('total_value').value;
        const remarks = document.getElementById('remarks').value;

        if (!productCode || !productName || !quantity || !price) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }

        const tbody = document.querySelector('table tbody');
        const newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td class="py-2 px-4 border-b">${productCode}</td>
            <td class="py-2 px-4 border-b">${productName}</td>
            <td class="py-2 px-4 border-b">${productFormat}</td>
            <td class="py-2 px-4 border-b">${packingSize}</td>
            <td class="py-2 px-4 border-b">${quantity}</td>
            <td class="py-2 px-4 border-b">${price}</td>
            <td class="py-2 px-4 border-b">${totalValue}</td>
            <td class="py-2 px-4 border-b">${remarks}</td>
            <td class="py-2 px-4 border-b text-center">
                <button onclick="deleteRow(this)" class="bg-red-500 text-white px-2 py-1 rounded">ลบ</button>
            </td>
        `;

        tbody.appendChild(newRow);

        document.getElementById('product_code').value = '';
        document.getElementById('product_name').value = '';
        document.getElementById('product_format').value = '';
        document.getElementById('packing_size').value = '';
        document.getElementById('quantity').value = '';
        document.getElementById('price').value = '';
        document.getElementById('total_value').value = '';
        document.getElementById('remarks').value = '';
    }

    function deleteRow(button) {
        const row = button.parentNode.parentNode;
        row.remove();
    }

    function convertDate(inputDate) {
    const parts = inputDate.split('/');
    const day = parts[0];
    const month = parts[1];
    const year = parts[2] - 543; // แปลงปี พ.ศ. เป็น ค.ศ.

    return `${year}-${month}-${day} ${new Date().toTimeString().split(' ')[0]}`;
}

function saveDashboardData() {
    const dateInputElement = document.querySelector("input[name='withdraw_date']");
    const deptInputElement = document.querySelector("input[name='department']");

    // ตรวจสอบว่าองค์ประกอบที่เลือกมีอยู่จริงก่อนเข้าถึงค่า
    if (!dateInputElement || !deptInputElement) {
        alert("ไม่พบข้อมูลวันที่หรือหน่วยงาน กรุณาตรวจสอบฟอร์มอีกครั้ง");
        return;
    }

    const date = convertDate(dateInputElement.value);
    const dept_id = deptInputElement.value;

    const tableRows = document.querySelectorAll("table tbody tr");
    let items = [];

    tableRows.forEach(row => {
        const cells = row.querySelectorAll("td");

        if (cells.length >= 8) {
            let item = {
                working_code: cells[0].innerText.trim(),
                item_code: cells[1].innerText.trim(),
                format_item_code: cells[2].innerText.trim(),
                packing_size: cells[3].innerText.trim(),
                quantity: parseInt(cells[4].innerText.trim()),
                price: parseFloat(cells[5].innerText.trim()),
                total_value: parseFloat(cells[6].innerText.trim()),
                remarks: cells[7].innerText.trim()
            };
            items.push(item);
        } else {
            console.warn("ข้อมูลแถวไม่ครบถ้วน");
        }
    });

    // ตรวจสอบว่า items มีข้อมูลหรือไม่
    if (items.length === 0) {
        alert("ไม่มีรายการสินค้าในตาราง กรุณาเพิ่มรายการก่อนบันทึก");
        return;
    }

    // ขอเลขที่ใบเบิกใหม่จากเซิร์ฟเวอร์ก่อนบันทึก
    fetch("generate_po_number.php")
        .then(response => response.json())
        .then(data => {
            if (!data.po_number) {
                alert("เกิดข้อผิดพลาดในการดึงเลขที่ใบเบิกใหม่");
                return;
            }

            const po_number = data.po_number;

            // ส่งข้อมูลไปที่ submit_po_items.php
            fetch("submit_po_items.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ po_number, date, dept_id, items })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("บันทึกข้อมูลสำเร็จ");
                    clearFormAndDashboard(); // เรียกใช้ฟังก์ชันเคลียร์ข้อมูล
                } else {
                    alert("เกิดข้อผิดพลาด: " + (data.message || "ไม่สามารถบันทึกข้อมูลได้"));
                }
            })
            .catch(error => {
                console.error("Error sending data:", error);
                alert("เกิดข้อผิดพลาดในการส่งข้อมูล");
            });
        })
        .catch(error => {
            console.error("Error fetching po_number:", error);
            alert("เกิดข้อผิดพลาดในการดึงเลขที่ใบเบิก");
        });
}

function clearFormAndDashboard() {
    // ล้างฟิลด์ฟอร์มทั้งหมด
    document.getElementById('product_code').value = '';
    document.getElementById('product_name').value = '';
    document.getElementById('product_format').value = '';
    document.getElementById('packing_size').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('price').value = '';
    document.getElementById('total_value').value = '';
    document.getElementById('remarks').value = '';

    // ล้างข้อมูลในตารางแดชบอร์ด
    const tbody = document.querySelector('table tbody');
    tbody.innerHTML = ''; // ล้างเนื้อหาใน tbody

    // ล้างข้อมูลใน array items
    items = [];
}

</script>

</body>
</html>
