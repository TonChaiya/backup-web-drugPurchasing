<?php
// ใช้ environment variables ในการเก็บค่าที่สำคัญ
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'user';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

try {
    $con = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // บันทึกข้อผิดพลาดลงไฟล์ log และแสดงข้อความทั่วไปให้กับผู้ใช้
    error_log("Database connection failed: " . $e->getMessage(), 0);
    echo "Connection failed. Please try again later.";
    exit; // หยุดการทำงานหากการเชื่อมต่อล้มเหลว
}