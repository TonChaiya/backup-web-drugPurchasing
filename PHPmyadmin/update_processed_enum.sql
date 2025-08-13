-- อัปเดต ENUM ของตาราง processed ให้รองรับสถานะทั้งหมด

ALTER TABLE `processed` 
MODIFY `status` enum(
    'อนุมัติ',
    'รออนุมัติ',
    'ยกเลิกใบเบิก',
    'Complete',
    'Completed',
    'ไตรมาสที่ 1 Complete',
    'ไตรมาสที่ 2 Complete'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

-- ตรวจสอบการเปลี่ยนแปลง
DESCRIBE processed;
