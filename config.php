<?php
// ใช้ environment variables ในการเก็บค่าที่สำคัญ
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'user';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

try {
    // สร้างการเชื่อมต่อ PDO
    $con = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // ตั้งค่า PDO attributes
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // แสดงข้อผิดพลาดเป็น exceptions
    $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // ปิดการ emulate prepared statements เพื่อป้องกัน SQL Injection
    $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // ตั้งค่า default fetch mode เป็น associative array

} catch (PDOException $e) {
    // บันทึกข้อผิดพลาดลงไฟล์ log
    error_log("Database connection failed: " . $e->getMessage(), 0);

    // แสดงข้อความทั่วไปให้กับผู้ใช้
    if (getenv('APP_ENV') === 'development') {
        // ในโหมด development แสดงข้อผิดพลาดละเอียด
        die("Connection failed: " . $e->getMessage());
    } else {
        // ในโหมด production แสดงข้อความทั่วไป
        die("Connection failed. Please try again later.");
    }
}
?>