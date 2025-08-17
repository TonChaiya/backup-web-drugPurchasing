<?php
session_start();
include('../config.php');

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ดึงข้อมูลผู้ใช้งานจากฐานข้อมูล
try {
    $stmt = $con->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}

$alertMessage = ''; // ตัวแปรเก็บข้อความแจ้งเตือน

function isValidPhone($phone) {
    return preg_match('/^[0-9]{9,10}$/', $phone);
}
function isValidHospitalCode($code) {
    return preg_match('/^[A-Za-z0-9\-]{3,10}$/', $code);
}
function isStrongPassword($password) {
    return strlen($password) >= 8 && preg_match('/[A-Z]/i', $password) && preg_match('/[0-9]/', $password) && preg_match('/[\W]/', $password);
}

// ฟังก์ชันแก้ไขรหัสผ่าน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_password_user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $alertMessage = "CSRF validation failed!";
    } else {
        $edit_user_id = intval($_POST['edit_password_user_id']);
        $new_password = trim($_POST['new_password']);
        $confirm_new_password = trim($_POST['confirm_new_password']);
        if (!empty($new_password) && $new_password === $confirm_new_password && isStrongPassword($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            try {
                $stmt = $con->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $edit_user_id);
                $stmt->execute();
                $alertMessage = "เปลี่ยนรหัสผ่านสำเร็จ!";
            } catch (PDOException $e) {
                $alertMessage = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
            }
        } else {
            $alertMessage = "รหัสผ่านใหม่ไม่ตรงกันหรือไม่ปลอดภัย (ต้องมีอย่างน้อย 8 ตัวอักษร, ตัวเลข, ตัวอักษรพิเศษ)!";
        }
    }
}

// ดึงข้อมูลผู้ใช้ที่ต้องการแก้ไข ถ้ามีการเลือก edit_id
$editUser = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    try {
        $stmt = $con->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $edit_id);
        $stmt->execute();
        $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $alertMessage = "เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage());
    }
}

// ฟังก์ชันบันทึกการแก้ไขข้อมูลผู้ใช้
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $alertMessage = "CSRF validation failed!";
    } else {
        $update_user_id = intval($_POST['update_user_id']);
        $username_account = trim($_POST['edit_username']);
        $hospital_code = trim($_POST['edit_hospital_code']);
        $hospital_name = trim($_POST['edit_hospital_name']);
        $responsible_person = trim($_POST['edit_responsible_person']);
        $contact_number = trim($_POST['edit_contact_number']);
        $hospital_contact_number = trim($_POST['edit_hospital_contact_number']);
        $new_password = trim($_POST['edit_password']);
        $confirm_new_password = trim($_POST['edit_confirm_password']);

        if (!empty($username_account) && !empty($hospital_code) && !empty($hospital_name) &&
            !empty($responsible_person) && !empty($contact_number) && !empty($hospital_contact_number) &&
            isValidHospitalCode($hospital_code) && isValidPhone($contact_number) && isValidPhone($hospital_contact_number)) {
            try {
                // ตรวจสอบข้อมูลซ้ำ (ยกเว้นตัวเอง)
                $stmt = $con->prepare("SELECT * FROM users WHERE (username_account = :username_account OR hospital_code = :hospital_code OR hospital_name = :hospital_name) AND id != :id");
                $stmt->bindParam(':username_account', $username_account);
                $stmt->bindParam(':hospital_code', $hospital_code);
                $stmt->bindParam(':hospital_name', $hospital_name);
                $stmt->bindParam(':id', $update_user_id);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $alertMessage = "ข้อมูลซ้ำ! ชื่อผู้ใช้, รหัสสถานพยาบาล หรือชื่อ รพ.สต นี้มีอยู่ในระบบแล้ว";
                } else {
                    // ถ้ามีการกรอกรหัสผ่านใหม่และตรงกัน ให้เปลี่ยนรหัสผ่านด้วย
                    if (!empty($new_password)) {
                        if ($new_password === $confirm_new_password && isStrongPassword($new_password)) {
                            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                            $stmt = $con->prepare("
                                UPDATE users SET 
                                    username_account = :username_account,
                                    password = :password,
                                    hospital_code = :hospital_code,
                                    hospital_name = :hospital_name,
                                    responsible_person = :responsible_person,
                                    contact_number = :contact_number,
                                    hospital_contact_number = :hospital_contact_number
                                WHERE id = :id
                            ");
                            $stmt->bindParam(':username_account', $username_account);
                            $stmt->bindParam(':password', $hashed_password);
                            $stmt->bindParam(':hospital_code', $hospital_code);
                            $stmt->bindParam(':hospital_name', $hospital_name);
                            $stmt->bindParam(':responsible_person', $responsible_person);
                            $stmt->bindParam(':contact_number', $contact_number);
                            $stmt->bindParam(':hospital_contact_number', $hospital_contact_number);
                            $stmt->bindParam(':id', $update_user_id);
                            $stmt->execute();
                            $alertMessage = "แก้ไขข้อมูลและรหัสผ่านสำเร็จ!";
                        } else {
                            $alertMessage = "รหัสผ่านใหม่ไม่ตรงกันหรือไม่ปลอดภัย!";
                        }
                    } else {
                        // ไม่เปลี่ยนรหัสผ่าน
                        $stmt = $con->prepare("
                            UPDATE users SET 
                                username_account = :username_account,
                                hospital_code = :hospital_code,
                                hospital_name = :hospital_name,
                                responsible_person = :responsible_person,
                                contact_number = :contact_number,
                                hospital_contact_number = :hospital_contact_number
                            WHERE id = :id
                        ");
                        $stmt->bindParam(':username_account', $username_account);
                        $stmt->bindParam(':hospital_code', $hospital_code);
                        $stmt->bindParam(':hospital_name', $hospital_name);
                        $stmt->bindParam(':responsible_person', $responsible_person);
                        $stmt->bindParam(':contact_number', $contact_number);
                        $stmt->bindParam(':hospital_contact_number', $hospital_contact_number);
                        $stmt->bindParam(':id', $update_user_id);
                        $stmt->execute();
                        $alertMessage = "แก้ไขข้อมูลสำเร็จ!";
                    }
                }
            } catch (PDOException $e) {
                $alertMessage = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
            }
        } else {
            $alertMessage = "กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง!";
        }
    }
}

// ลบผู้ใช้
if (isset($_POST['delete_user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $alertMessage = "CSRF validation failed!";
    } else {
        $delete_user_id = intval($_POST['delete_user_id']);
        // ตรวจสอบว่าเป็น admin คนสุดท้ายหรือไม่
        $stmt = $con->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        $stmt = $con->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $delete_user_id);
        $stmt->execute();
        $role = $stmt->fetchColumn();
        if ($role === 'admin' && $adminCount <= 1) {
            $alertMessage = "ไม่สามารถลบ admin คนสุดท้ายได้!";
        } else {
            try {
                $stmt = $con->prepare("DELETE FROM users WHERE id = :id");
                $stmt->bindParam(':id', $delete_user_id);
                $stmt->execute();
                $alertMessage = "ลบผู้ใช้สำเร็จ!";
            } catch (PDOException $e) {
                $alertMessage = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
            }
        }
    }
}

// ปรับสถานะผู้ใช้ (admin/user)
if (isset($_POST['toggle_role_user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $alertMessage = "CSRF validation failed!";
    } else {
        $toggle_user_id = intval($_POST['toggle_role_user_id']);
        $current_role = $_POST['current_role'];
        $new_role = $current_role === 'admin' ? 'user' : 'admin';

        // ตรวจสอบกรณีลดระดับ admin คนสุดท้าย
        if ($current_role === 'admin' && $new_role === 'user') {
            $stmt = $con->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $stmt->execute();
            $adminCount = $stmt->fetchColumn();
            if ($adminCount <= 1) {
                $alertMessage = "ไม่สามารถลดระดับ admin คนสุดท้ายเป็นผู้ใช้ได้!";
            } else {
                try {
                    $stmt = $con->prepare("UPDATE users SET role = :role WHERE id = :id");
                    $stmt->bindParam(':role', $new_role);
                    $stmt->bindParam(':id', $toggle_user_id);
                    $stmt->execute();
                    $alertMessage = "ปรับสถานะผู้ใช้สำเร็จ!";
                } catch (PDOException $e) {
                    $alertMessage = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
                }
            }
        } else {
            try {
                $stmt = $con->prepare("UPDATE users SET role = :role WHERE id = :id");
                $stmt->bindParam(':role', $new_role);
                $stmt->bindParam(':id', $toggle_user_id);
                $stmt->execute();
                $alertMessage = "ปรับสถานะผู้ใช้สำเร็จ!";
            } catch (PDOException $e) {
                $alertMessage = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
            }
        }
    }
}

// ฟังก์ชันลงทะเบียนผู้ใช้ใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && !isset($_POST['edit_password_user_id']) 
    && !isset($_POST['update_user_id']) 
    && !isset($_POST['delete_user_id']) 
    && !isset($_POST['toggle_role_user_id'])
) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $alertMessage = "CSRF validation failed!";
    } else {
        $username_account = trim($_POST['username']);
        $password_raw = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $hospital_code = trim($_POST['hospital_code']);
        $hospital_name = trim($_POST['hospital_name']);
        $responsible_person = trim($_POST['responsible_person']);
        $contact_number = trim($_POST['contact_number']);
        $hospital_contact_number = trim($_POST['hospital_contact_number']);

        if (!empty($username_account) && !empty($password_raw) && !empty($confirm_password) && !empty($hospital_code) && !empty($hospital_name) &&
            !empty($responsible_person) && !empty($contact_number) && !empty($hospital_contact_number) &&
            isValidHospitalCode($hospital_code) && isValidPhone($contact_number) && isValidPhone($hospital_contact_number)) {
            if ($password_raw === $confirm_password && isStrongPassword($password_raw)) {
                $password = password_hash($password_raw, PASSWORD_BCRYPT);
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
                            (username_account, password, hospital_code, hospital_name, responsible_person, contact_number, hospital_contact_number, role) 
                            VALUES 
                            (:username_account, :password, :hospital_code, :hospital_name, :responsible_person, :contact_number, :hospital_contact_number, 'user')
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
                    $alertMessage = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
                }
            } else {
                $alertMessage = "กรุณากรอกรหัสผ่านและยืนยันรหัสผ่านให้ตรงกัน และรหัสผ่านต้องปลอดภัย!";
            }
        } else {
            $alertMessage = "กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง!";
        }
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
    <style>
        /* เพิ่ม responsive ให้ sidebar และ main content */
        @media (max-width: 1024px) {
            .sidebar-user {
                width: 100%;
                min-width: 0;
                max-width: none;
                margin-bottom: 1rem;
            }
            .main-user-content {
                width: 100%;
                min-width: 0;
                max-width: none;
            }
            .flex-user-wrap {
                flex-direction: column;
            }
        }
        @media (max-width: 640px) {
            .sidebar-user {
                padding: 0.5rem;
            }
            .main-user-content {
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen">
    <?php include('nav.php'); ?>

    <div class="flex flex-user-wrap min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar-user w-1/4 min-w-[220px] bg-gray-800 text-white p-4">
            <h2 class="text-lg font-bold mb-4">รายชื่อผู้ใช้งาน</h2>
            <ul>
                <?php foreach ($users as $user): ?>
                    <li class="mb-2">
                        <div class="flex items-center gap-2">
                            <a href="?edit_id=<?php echo $user['id']; ?>" class="py-2 px-4 bg-gray-700 rounded hover:bg-blue-700 cursor-pointer flex-1 flex items-center">
                                <?php echo htmlspecialchars($user['hospital_name']); ?> (<?php echo htmlspecialchars($user['username_account']); ?>)
                                <span class="ml-2 text-xs px-2 py-1 rounded <?php echo $user['role'] === 'admin' ? 'bg-yellow-500' : 'bg-gray-500'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                </span>
                            </a>
                            <!-- ปุ่มลบ -->
                            <form method="post" onsubmit="return confirm('คุณต้องการลบผู้ใช้นี้ใช่หรือไม่?');">
                                <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700 ml-1">ลบ</button>
                            </form>
                            <!-- ปุ่มปรับสถานะ -->
                            <form method="post">
                                <input type="hidden" name="toggle_role_user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="current_role" value="<?php echo $user['role']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 ml-1">
                                    <?php echo $user['role'] === 'admin' ? 'เปลี่ยนเป็น User' : 'เปลี่ยนเป็น Admin'; ?>
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-user-content flex-1 flex justify-center items-center p-6">
            <?php if ($editUser): ?>
                <!-- ฟอร์มแก้ไขข้อมูลผู้ใช้ -->
                <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md text-center">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">แก้ไขข้อมูลผู้ใช้</h2>
                    <div class="mb-3 text-left text-sm text-gray-700">
                        <strong>ID:</strong> <?php echo $editUser['id']; ?>
                    </div>
                    <input type="hidden" name="update_user_id" value="<?php echo $editUser['id']; ?>">
                    <div class="mb-2 text-left text-sm text-gray-700">Username</div>
                    <input type="text" name="edit_username" value="<?php echo htmlspecialchars($editUser['username_account']); ?>" placeholder="Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">รหัสสถานพยาบาล</div>
                    <input type="text" name="edit_hospital_code" value="<?php echo htmlspecialchars($editUser['hospital_code']); ?>" placeholder="รหัสสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">ชื่อ รพ.สต</div>
                    <input type="text" name="edit_hospital_name" value="<?php echo htmlspecialchars($editUser['hospital_name']); ?>" placeholder="ชื่อ รพ.สต" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">ผู้รับผิดชอบ</div>
                    <input type="text" name="edit_responsible_person" value="<?php echo htmlspecialchars($editUser['responsible_person']); ?>" placeholder="ผู้รับผิดชอบ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">เบอร์ติดต่อ</div>
                    <input type="text" name="edit_contact_number" value="<?php echo htmlspecialchars($editUser['contact_number']); ?>" placeholder="เบอร์ติดต่อ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">เบอร์ติดต่อสถานพยาบาล</div>
                    <input type="text" name="edit_hospital_contact_number" value="<?php echo htmlspecialchars($editUser['hospital_contact_number']); ?>" placeholder="เบอร์ติดต่อสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">รหัสผ่านใหม่ (ถ้าเปลี่ยน)</div>
                    <input type="password" name="edit_password" placeholder="รหัสผ่านใหม่ (ถ้าเปลี่ยน)" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="mb-2 text-left text-sm text-gray-700">ยืนยันรหัสผ่านใหม่</div>
                    <input type="password" name="edit_confirm_password" placeholder="ยืนยันรหัสผ่านใหม่" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                    <div class="text-left text-xs text-gray-500 mb-3">
                        * รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร, มีตัวเลข, ตัวอักษรภาษาอังกฤษ และอักขระพิเศษ
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">บันทึกการแก้ไข</button>
                    <a href="admin_register.php" class="block mt-4 text-blue-600 hover:underline">กลับ</a>
                </form>
            <?php else: ?>
                <!-- ฟอร์มลงทะเบียนผู้ใช้ใหม่ -->
                <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center" onsubmit="clearForm(this)">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Register</h2>
                    <div class="mb-2 text-left text-sm text-gray-700">Username</div>
                    <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="mb-2 text-left text-sm text-gray-700">Password</div>
                    <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="mb-2 text-left text-sm text-gray-700">Confirm Password</div>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="text-left text-xs text-gray-500 mb-3">
                        * รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร, มีตัวเลข, ตัวอักษรภาษาอังกฤษ และอักขระพิเศษ
                    </div>
                    <div class="mb-2 text-left text-sm text-gray-700">รหัสสถานพยาบาล</div>
                    <input type="text" name="hospital_code" placeholder="รหัสสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="mb-2 text-left text-sm text-gray-700">ชื่อ รพ.สต</div>
                    <input type="text" name="hospital_name" placeholder="ชื่อ รพ.สต" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="mb-2 text-left text-sm text-gray-700">ผู้รับผิดชอบ</div>
                    <input type="text" name="responsible_person" placeholder="ผู้รับผิดชอบ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="mb-2 text-left text-sm text-gray-700">เบอร์ติดต่อ</div>
                    <input type="text" name="contact_number" placeholder="เบอร์ติดต่อ" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="mb-2 text-left text-sm text-gray-700">เบอร์ติดต่อสถานพยาบาล</div>
                    <input type="text" name="hospital_contact_number" placeholder="เบอร์ติดต่อสถานพยาบาล" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Register</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($alertMessage): ?>
        <script>
            Swal.fire({
                title: "แจ้งเตือน",
                text: "<?php echo $alertMessage; ?>",
                icon: "<?php echo (
                    $alertMessage === 'บันทึกข้อมูลสำเร็จ!' ||
                    $alertMessage === 'เปลี่ยนรหัสผ่านสำเร็จ!' ||
                    $alertMessage === 'แก้ไขข้อมูลสำเร็จ!' ||
                    $alertMessage === 'แก้ไขข้อมูลและรหัสผ่านสำเร็จ!' ||
                    $alertMessage === 'ลบผู้ใช้สำเร็จ!' ||
                    $alertMessage === 'ปรับสถานะผู้ใช้สำเร็จ!'
                ) ? 'success' : 'error'; ?>",
                confirmButtonText: "ตกลง"
            }).then(() => {
                <?php if (
                    $alertMessage === 'บันทึกข้อมูลสำเร็จ!' ||
                    $alertMessage === 'เปลี่ยนรหัสผ่านสำเร็จ!' ||
                    $alertMessage === 'แก้ไขข้อมูลสำเร็จ!' ||
                    $alertMessage === 'แก้ไขข้อมูลและรหัสผ่านสำเร็จ!' ||
                    $alertMessage === 'ลบผู้ใช้สำเร็จ!' ||
                    $alertMessage === 'ปรับสถานะผู้ใช้สำเร็จ!'
                ): ?>
                    window.location.href = "../admin/admin_register.php";
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>
