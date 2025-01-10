<?php
session_start();
include('../config.php');

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

<?php include('nav.php'); ?>

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
                window.location.href = "../admin/admin_register.php";
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