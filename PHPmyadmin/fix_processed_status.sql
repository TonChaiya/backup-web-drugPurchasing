-- แก้ไขตาราง processed เพื่อรองรับสถานะทั้งหมด
-- เปลี่ยนจาก ENUM เป็น VARCHAR เพื่อความยืดหยุ่น

ALTER TABLE `processed` 
MODIFY `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

-- ตรวจสอบการเปลี่ยนแปลง
DESCRIBE processed;
