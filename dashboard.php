<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['hospital_name'])) {
    header("Location: login.php");
    exit;
}

// ดึงข้อมูล hospital_name จากเซสชัน
$hospital_name = $_SESSION['hospital_name'];

// กำหนดค่าเริ่มต้นให้ตัวแปร $statusCounts
$statusCounts = [
    'รออนุมัติ' => 0,
    'อนุมัติ' => 0,
];

try {
    // ดึงข้อมูลจำนวนใบเบิกตามสถานะและเฉพาะใบเบิกของ hospital_name ของตนเอง
    $stmt = $con->prepare("SELECT status, COUNT(DISTINCT po_number) as count
                           FROM po
                           WHERE dept_id = :hospital_name
                           GROUP BY status");
    $stmt->bindParam(':hospital_name', $hospital_name, PDO::PARAM_STR);
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
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

<nav class="bg-gray-800">
  <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
    <div class="relative flex h-16 items-center justify-between">
      <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
        <!-- Mobile menu button-->
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
            <a href="withdrawUser.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
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

        <!-- Profile dropdown -->
        <div class="relative ml-3">
          <div>
            <button type="button" class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
              <span class="absolute -inset-1.5"></span>
              <span class="sr-only">Open user menu</span>
              <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
            </button>
          </div>

          <!-- Dropdown menu, hidden initially -->
          <div id="dropdown-menu" class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Your Profile</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Settings</a>
            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Sign out</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile menu -->
  <div class="sm:hidden" id="mobile-menu">
    <div class="space-y-1 px-2 pb-3 pt-2">
      <a href="dashboard.php" class="block rounded-md bg-gray-900 px-3 py-2 text-base font-medium text-white" aria-current="page">Dashboard</a>
      <a href="page-report.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">รายงาน</a>
      <a href="withdrawUser.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
      <a href="register.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">register</a>
    </div>
  </div>
</nav>

<div class="container mx-auto mt-8"> <!-- dashboard แสดงสถานะเบิก -->
    <h2 class="text-3xl font-bold mb-6 text-center">User-สถานะใบเบิก</h2>
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

        // เรียก AJAX เพื่อดึงข้อมูลใบเบิกตามสถานะ
        fetch('get_po_by_status.php?status=' + encodeURIComponent(status))
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

    // ฟังก์ชัน approvePo สำหรับยืนยันใบเบิก
    function approvePo(poNumber) {
        if (confirm('คุณต้องการยืนยันใบเบิกนี้ใช่หรือไม่?')) {
            fetch(`approve_po.php?po_number=${poNumber}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('ใบเบิกได้รับการอนุมัติ');
                    location.reload(); // รีเฟรชหน้าเพื่ออัปเดตสถานะ
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการยืนยันใบเบิก');
            });
        }
    }

    //ฟังชั่นยกเลิกใบเบิก
    function cancelPo(poNumber) {
        if (confirm("คุณต้องการยกเลิกใบเบิกนี้ใช่หรือไม่?")) {
            fetch('cancel_po.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ po_number: poNumber, status: 'ยกเลิกใบเบิก' })  // เปลี่ยนเป็น 'ยกเลิกใบเบิก'
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
