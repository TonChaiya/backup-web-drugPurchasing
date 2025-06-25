-- สร้างตาราง processed สำหรับระบบ
CREATE TABLE `processed` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
