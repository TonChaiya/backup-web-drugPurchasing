<?php
session_start();
include_once 'config.php';

// สร้าง token สำหรับ CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ token CSRF
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $username = trim($_POST['username_account']);
        $password = trim($_POST['password']);

        // ดึงข้อมูลผู้ใช้พร้อมกับ role และ Location
        $stmt = $con->prepare("SELECT * FROM users WHERE username_account = :username_account");
        $stmt->bindParam(':username_account', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        // Debug: แสดงข้อมูลที่ดึงมา (ลบออกหลังจากแก้ไขเสร็จ)
        // var_dump($user); exit;

        if ($user) {
            // ตรวจสอบรหัสผ่าน - ถ้าเป็น plain text ให้เปรียบเทียบตรงๆ ก่อน
            $passwordMatch = false;

            // ลองตรวจสอบแบบ hash ก่อน
            if (password_verify($password, $user['password'])) {
                $passwordMatch = true;
            }
            // ถ้าไม่ตรง ลองเปรียบเทียบแบบ plain text
            elseif ($password === $user['password']) {
                $passwordMatch = true;
            }

            if ($passwordMatch) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username_account'] = $user['username_account'];
                $_SESSION['hospital_name'] = $user['hospital_name']; // เก็บ Location ในเซสชัน
                $_SESSION['role'] = $user['role']; // role: admin หรือ user

                // Redirect ตามบทบาท
                if ($user['role'] === 'admin') {
                    header("Location: admin/admin.dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $error = "ไม่พบชื่อผู้ใช้นี้ในระบบ";
        }
    } else {
        $error = "คำขอไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">เข้าสู่ระบบ</h2>
        <?php if (!empty($error)) : ?>
            <p class="text-red-500 mb-4 text-center"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="mb-4">
            <label for="username_account" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้</label>
            <input type="text" name="username_account" id="username_account" required
                class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
            <input type="password" name="password" id="password" required
                class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
            เข้าสู่ระบบ
        </button>
        <!-- <p class="mt-4 text-sm text-gray-600 text-center">
            หากคุณไม่มีบัญชี <a href="register.php" class="text-blue-500 hover:underline">ลงทะเบียน</a>
        </p> -->
    </form>
</body>

</html>