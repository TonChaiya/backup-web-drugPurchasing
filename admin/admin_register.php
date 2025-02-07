<?php
session_start();
include('../config.php');

// ดึงข้อมูลผู้ใช้งานจากฐานข้อมูล
try {
    $stmt = $con->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}

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

<body class="bg-gray-100">
    <?php include('nav.php'); ?>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-1/4 bg-gray-800 text-white p-4">
            <h2 class="text-lg font-bold mb-4">รายชื่อผู้ใช้งาน</h2>
            <ul>
                <?php foreach ($users as $user): ?>
                    <li class="mb-2">
                        <span class="block py-2 px-4 bg-gray-700 rounded"><?php echo htmlspecialchars($user['hospital_name']); ?> (<?php echo htmlspecialchars($user['username_account']); ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex justify-center items-center">
            <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center" onsubmit="clearForm(this)">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Register</h2>
                <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="text" name="hospital_code" placeholder="รหัสสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="text" name="hospital_name" placeholder="ชื่อ รพ.สต" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="text" name="responsible_person" placeholder="ผู้รับผิดชอบ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="text" name="contact_number" placeholder="เบอร์ติดต่อ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="text" name="hospital_contact_number" placeholder="เบอร์ติดต่อสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Register</button>
            </form>
        </div>
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
</body>

</html>
