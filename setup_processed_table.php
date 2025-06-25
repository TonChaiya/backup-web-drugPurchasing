<?php
include 'config.php';

try {
    // สร้างตาราง processed
    $sql = "CREATE TABLE IF NOT EXISTS `processed` (
      `id` int NOT NULL AUTO_INCREMENT,
      `working_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `item_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `format_item_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `total_quantity` int NOT NULL,
      `price` decimal(10,2) NOT NULL,
      `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
      `packing_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
      `total_value` decimal(15,2) NOT NULL,
      `status` enum('อนุมัติ','รออนุมัติ','ยกเลิกใบเบิก','Completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `purchase_status` enum('GPO','จัดซื้อบริษัท') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `processed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $con->exec($sql);
    echo "✅ สร้างตาราง processed สำเร็จ!<br>";
    
    // ตรวจสอบว่าตาราง account มีอยู่หรือไม่
    $checkAccount = "SHOW TABLES LIKE 'account'";
    $result = $con->query($checkAccount);
    
    if ($result->rowCount() == 0) {
        // สร้างตาราง account ถ้ายังไม่มี
        $accountSql = "CREATE TABLE IF NOT EXISTS `account` (
          `id_account` int NOT NULL AUTO_INCREMENT,
          `username_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
          `password_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
          `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
          `Location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
          PRIMARY KEY (`id_account`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $con->exec($accountSql);
        echo "✅ สร้างตาราง account สำเร็จ!<br>";
        
        // เพิ่มข้อมูลผู้ใช้ทดสอบ
        $insertAdmin = "INSERT INTO account (username_account, password_account, role, Location) VALUES 
                       ('admin', ?, 'admin', 'ทดสอบ admin'),
                       ('user', ?, 'user', 'ทดสอบ user')";
        $stmt = $con->prepare($insertAdmin);
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $userPassword = password_hash('user123', PASSWORD_DEFAULT);
        $stmt->execute([$adminPassword, $userPassword]);
        echo "✅ เพิ่มข้อมูลผู้ใช้ทดสอบสำเร็จ!<br>";
        echo "- admin / admin123 (role: admin)<br>";
        echo "- user / user123 (role: user)<br>";
    } else {
        echo "✅ ตาราง account มีอยู่แล้ว<br>";
    }
    
    echo "<br>🎉 การตั้งค่าเสร็จสมบูรณ์! คุณสามารถลบไฟล์นี้ได้แล้ว<br>";
    echo "<a href='index.php'>← กลับไปหน้าเข้าสู่ระบบ</a>";
    
} catch (PDOException $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>
