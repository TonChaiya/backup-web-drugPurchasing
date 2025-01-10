<?php
session_start();
include('config.php');

$alertMessage = ''; // ตัวแปรเก็บข้อความแจ้งเตือน

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_account = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); // เข้ารหัสรหัสผ่าน
    $hospital_code = trim($_POST['hospital_code']);
    $hospital_name = trim($_POST['hospital_name']);
    $responsible_person = trim($_POST['responsible_person']); // ผู้รับผิดชอบ
    $contact_number = trim($_POST['contact_number']); // เบอร์ติดต่อ
    $hospital_contact_number = trim($_POST['hospital_contact_number']); // เบอร์ติดต่อสถานพยาบาล

    if (!empty($username_account) && !empty($password) && !empty($hospital_code) && !empty($hospital_name) &&
        !empty($responsible_person) && !empty($contact_number) && !empty($hospital_contact_number)) {
        try {
            $stmt = $con->prepare("SELECT * FROM users WHERE username_account = :username_account OR hospital_code = :hospital_code OR hospital_name = :hospital_name");
            $stmt->bindParam(':username_account', $username_account);
            $stmt->bindParam(':hospital_code', $hospital_code);
            $stmt->bindParam(':hospital_name', $hospital_name);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $alertMessage = "ข้อมูลซ้ำ! ชื่อผู้ใช้, รหัสสถานพยาบาล หรือชื่อ รพ.สต นี้มีอยู่ในระบบแล้ว";
            } else {
                $stmt = $con->prepare("
                    INSERT INTO users 
                    (username_account, password, hospital_code, hospital_name, responsible_person, contact_number, hospital_contact_number) 
                    VALUES 
                    (:username_account, :password, :hospital_code, :hospital_name, :responsible_person, :contact_number, :hospital_contact_number)
                ");
                $stmt->bindParam(':username_account', $username_account);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':hospital_code', $hospital_code);
                $stmt->bindParam(':hospital_name', $hospital_name);
                $stmt->bindParam(':responsible_person', $responsible_person);
                $stmt->bindParam(':contact_number', $contact_number);
                $stmt->bindParam(':hospital_contact_number', $hospital_contact_number);
                $stmt->execute();

                $alertMessage = "บันทึกข้อมูลสำเร็จ!";
            }
        } catch (PDOException $e) {
            $alertMessage = "เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $alertMessage = "กรุณากรอกข้อมูลให้ครบถ้วน!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a href="dashboard-copy.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">เบิกยา</a>
        <a href="register.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">register</a>
      </div>
    </div>
  </nav>

  <div class="flex justify-center items-center min-h-screen">
    <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center" onsubmit="clearForm(this)">
      <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Register</h2>
      <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="text" name="hospital_code" placeholder="รหัสสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="text" name="hospital_name" placeholder="ชื่อ รพ.สต" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="text" name="responsible_person" placeholder="ผู้รับผิดชอบ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="text" name="contact_number" placeholder="เบอร์ติดต่อที่ติดต่อได้" pattern="\d{10}" title="กรุณากรอกเบอร์โทรศัพท์ 10 หลัก (เช่น 0812345678)" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <input type="text" name="hospital_contact_number" placeholder="เบอร์ติดต่อสถานพยาบาล" pattern="\d{9}" title="กรุณากรอกเบอร์โทรศัพท์สำนักงาน (เช่น 053-xxx-xxx)" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">Register</button>
    </form>
  </div>
  <?php if ($alertMessage): ?>
    <script>
        Swal.fire({
            title: "แจ้งเตือน",
            text: "<?php echo $alertMessage; ?>",
            icon: "<?php echo $alertMessage === 'บันทึกข้อมูลสำเร็จ!' ? 'success' : 'error'; ?>",
            confirmButtonText: "ตกลง"
        }).then(() => {
            <?php if ($alertMessage === 'บันทึกข้อมูลสำเร็จ!'): ?>
                window.location.href = "register.php";
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
  <script>
    function clearForm(form) {
      // รีเซ็ตฟอร์มหลังจากการส่งข้อมูลสำเร็จ
      setTimeout(function() {
        form.reset();
      }, 50); // ให้เวลาเซิร์ฟเวอร์ในการประมวลผลก่อนเคลียร์ฟอร์ม
    }

    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      const passwordInput = document.querySelector('input[name="password"]');
      const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');

      form.addEventListener('submit', function(event) {
        if (passwordInput.value !== confirmPasswordInput.value) {
          event.preventDefault(); // ป้องกันการส่งฟอร์ม
          alert('รหัสผ่านไม่ตรงกัน กรุณาลองใหม่');
        }
      });
    });
  </script>


  <!-- Script to toggle dropdown menu ซ่อนเมื่อไม่ได้กด -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const profileButton = document.getElementById('user-menu-button');
      const dropdownMenu = document.getElementById('dropdown-menu');

      profileButton.addEventListener('click', function(event) {
        event.preventDefault();
        dropdownMenu.classList.toggle('hidden');
      });

      document.addEventListener('click', function(event) {
        if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
          dropdownMenu.classList.add('hidden');
        }
      });
    });
  </script>

</body>

</html>