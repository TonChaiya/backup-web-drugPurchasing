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

<body class="bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center min-h-screen">
    <form method="POST" class="w-full max-w-md bg-white/80 backdrop-blur-xl border border-white/50 rounded-2xl shadow-2xl p-8">
        <h2 class="text-2xl font-extrabold mb-6 text-center text-blue-700">เข้าสู่ระบบ</h2>
        <?php if (!empty($error)) : ?>
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700 text-center">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="mb-4">
            <label for="username_account" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้</label>
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-blue-600 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5">
                        <circle cx="12" cy="8" r="3" />
                        <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                    </svg>
                </span>
                <input type="text" name="username_account" id="username_account" required autocomplete="username"
                    class="w-full pl-10 pr-4 py-2 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
        </div>
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-blue-600 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5">
                        <path d="M6 10V8a6 6 0 1112 0v2" />
                        <rect x="6" y="10" width="12" height="10" rx="2" ry="2" />
                    </svg>
                </span>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                    class="w-full pl-10 pr-10 py-2 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">

                <button type="button" id="togglePassword" aria-label="สลับการแสดงรหัสผ่าน"
                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                    <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 hidden">
                        <path d="M1 12s4-7 11-7c2.5 0 4.7.9 6.4 2" />
                        <path d="M23 12s-4 7-11 7c-2.5 0-4.7-.9-6.4-2" />
                        <circle cx="12" cy="12" r="3" />
                        <path d="M3 3l18 18" />
                    </svg>
                </button>
            </div>
        </div>
        <button id="loginBtn" type="submit" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-xl shadow-sm hover:shadow-md hover:bg-blue-700 transition">
            เข้าสู่ระบบ
        </button>
        <!-- <p class="mt-4 text-sm text-gray-600 text-center">
            หากคุณไม่มีบัญชี <a href="register.php" class="text-blue-500 hover:underline">ลงทะเบียน</a>
        </p> -->
    </form>
    <script>
      (function(){
        const toggleBtn = document.getElementById('togglePassword');
        const pwd = document.getElementById('password');
        const eye = document.getElementById('icon-eye');
        const eyeOff = document.getElementById('icon-eye-off');
        if (toggleBtn && pwd && eye && eyeOff) {
          toggleBtn.addEventListener('click', function(){
            const showing = pwd.type === 'text';
            pwd.type = showing ? 'password' : 'text';
            eye.classList.toggle('hidden', !showing);
            eyeOff.classList.toggle('hidden', showing);
          });
        }
        const form = document.querySelector('form');
        const btn = document.getElementById('loginBtn');
        if (form && btn) {
          form.addEventListener('submit', function(){
            btn.disabled = true; btn.classList.add('opacity-70','cursor-not-allowed');
            btn.textContent = 'กำลังเข้าสู่ระบบ...';
          });
        }
      })();
    </script>

</body>

</html>