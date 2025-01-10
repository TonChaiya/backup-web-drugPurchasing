-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: fdb1030.awardspace.net
-- Generation Time: Nov 18, 2024 at 11:14 AM
-- Server version: 8.0.32
-- PHP Version: 8.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `4540623_lpao`
--

-- --------------------------------------------------------

--
-- Table structure for table `drug_list`
--

CREATE TABLE `drug_list` (
  `id_code` int NOT NULL,
  `working_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name_item_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `format_item` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `packing_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price_unit_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drug_list`
--

INSERT INTO `drug_list` (`id_code`, `working_code`, `name_item_code`, `format_item`, `packing_code`, `price_unit_code`) VALUES
(852, '1000010', 'Acyclovir 400 mg tab', ' กล่อง', '70 เม็ด/กล่อง', '198'),
(853, '1000020', 'Albendazole 200 mg tab               ', ' กล่อง', '100 เม็ด/กล่อง', '165'),
(854, '1000030', 'Amitryptyline HCl 10 mg tab                 ', ' กล่อง', '500เม็ด/กล่อง', '166.92'),
(855, '1000040', 'Amoxycillin trihydrate 250 mg cap                ', ' กล่อง', '500เม็ด/กล่อง', '480.43'),
(856, '1000050', 'Amoxycillin trihydrate 500 mg cap      ', ' กล่อง', '500เม็ด/กล่อง', '850.65'),
(857, '1000060', 'Betahistine 6 mg tab', ' แผง', '10 เม็ด/แผง', '4.7'),
(858, '1000070', 'Bisacodyl 5 mg enteric coated tab                 ', 'กล่อง', '100 เม็ด/กล่อง', '150'),
(859, '1000080', 'Calcium carbonate 600 mg tab', ' กระปุก', '500 เม็ด/กระปุก', '250'),
(860, '1000090', 'Cetirizine 10 mg tab', ' แผง', '10 เม็ด/แผง', '2.5'),
(861, '1000100', 'Chlorpheniramine maleate 4 mg tab', ' กล่อง', '100 เม็ด/กล่อง', '6.9'),
(862, '1000110', 'Clindamycin 300 mg cap', ' กล่อง', '100 เม็ด/กล่อง', '260'),
(863, '1000120', 'Dextromethorphan15 mg tab', ' กระปุก', '1000 เม็ด/กระปุก', '450'),
(864, '1000130', 'Diazepam 2 mg tab             ', ' กระปุก', '500 เม็ด/กระปุก', '90'),
(865, '1000140', 'Diclofenac sodium 25 mg tab         ', ' กระปุก', '1000 เม็ด/กระปุก', '120'),
(866, '1000150', 'Dicloxacillin sodium 250 mg cap', ' กล่อง', '500เม็ด/กล่อง', '551.05'),
(867, '1000160', 'Dimenhydrinate 50 mg tab               ', ' กระปุก', '1000 เม็ด/กระปุก', '165'),
(868, '1000170', 'Domperidone maleate 10 mg tab             ', ' กระปุก', '1000 เม็ด/กระปุก', '160'),
(869, '1000180', 'Ergotamine1 mg + Caffeine 100 mg tab', ' แผง', '10 เม็ด/แผง', '24'),
(870, '1000190', 'Ferrous fumarate 200 mg tab', ' กระปุก', '1000 เม็ด/กระปุก', '140'),
(871, '1000200', 'Fluoxetine hydrochloride 20 mg cap', ' กล่อง', '500เม็ด/กล่อง', '249.31'),
(872, '1000210', 'Folic acid 5 mg tab                    ', ' กระปุก', '1000 เม็ด/กระปุก', '200'),
(873, '1000220', 'Glyceryl guaiacolate  100 mg  Tab', ' แผง', '10 เม็ด/แผง', '5'),
(874, '1000230', 'Griseofulvin 500 mg tab', 'กล่อง', '500 เม็ด/กล่อง', '950'),
(875, '1000240', 'Hydroxyzine hydrochloride 10 mg tab', ' กระปุก', '1000 เม็ด/กระปุก', '130'),
(876, '1000250', 'Hyoscine-N-butylbromide 10 mg tab', 'กล่อง', '100 เม็ด/กล่อง', '95'),
(877, '1000260', 'Ibuprofen 400 mg flim coated tab ', ' กระปุก', '1000 เม็ด/กระปุก', '370'),
(878, '1000270', 'Loratadine 10 mg tab', 'กล่อง', '10 เม็ด/แผง*10แผง', '55'),
(879, '1000280', 'Lorazepam 1 mg tab', ' กระปุก', '1000 เม็ด/กระปุก', '350'),
(880, '1000290', 'Metronidazole 200 mg tab         ', 'กล่อง', '500 เม็ด/กล่อง', '350'),
(881, '1000300', 'Norfloxacin 400 mg  tab           ', ' กระปุก', '100 เม็ด/กระปุก', '100'),
(882, '1000310', 'Omeprazole  20 mg cap     ', ' กล่อง', '100 เม็ด/กล่อง', '55'),
(883, '1000320', 'Paracetamol 325 mg tab               ', ' กระปุก', '1000 เม็ด/กระปุก', '180'),
(884, '1000330', 'Paracetamol 500 mg tab                 ', ' กล่อง', '500เม็ด/กล่อง', '225'),
(885, '1000340', 'Penicillin V 250 mg (400,000 U) Tab', 'กล่อง', '100 เม็ด/กล่อง', '100'),
(886, '1000350', 'Prednisolone 5 mg tab               ', ' กระปุก', '500 เม็ด/กระปุก', '175.58'),
(887, '1000360', 'Roxithromycin 150 mg tab', ' แผง', '10 เม็ด/แผง', '12'),
(888, '1000370', 'Simethicone 80 mg chewable tab', ' แผง', '10 เม็ด/แผง', '5.8'),
(889, '1000380', 'Tramadol hydrochloride 50 mg cap', ' แผง', '10 เม็ด/แผง', '18'),
(890, '1000390', 'Triferdine 150 tab (Potassium iodine 0.15 mg.+Folic acid 0.4 mg.+Iron 60.81 mg.)', 'ขวด', '30 เม็ด/ขวด', '26.75'),
(891, '1000400', 'Vitamin B1 (Thiamine HCl) 100 mg Tab              ', ' กระปุก', '1000 เม็ด/กระปุก', '395'),
(892, '1000410', 'Albendazole 100 mg/5 mL susp- 30 mL', 'ขวด', '1 ขวด', '14.45'),
(893, '1000420', 'Amoxycillin  125 mg/5 mL dry syr- 60 mL', 'ขวด', '1 ขวด', '13'),
(894, '1000430', 'Chlorpheniramine 2 mg/5 mL syr- 60 mL', 'ขวด', '1 ขวด', '7.49'),
(895, '1000440', 'Dextromethorphan 5mg/5 mL syr- 60 mL', 'ขวด', '1 ขวด', '15'),
(896, '1000450', 'Dicloxacillin 62.5 mg/5 mL dry syr- 60 mL', 'ขวด', '1 ขวด', '22'),
(897, '1000460', 'Domperidone 5 mg/5 mL susp- 30 mL', 'ขวด', '1 ขวด', '7.5'),
(898, '1000470', 'Erythromycin 125 mg/5mL dry syr-60mL  ', 'ขวด', '1 ขวด', '17'),
(899, '1000480', 'Ferrous fumarate 45 mg/0.6 mL syr (drop) -15 mL', 'ขวด', '1 ขวด', '27'),
(900, '1000490', 'Glyceryl guaiacolate100mg/5ml syr-60 mL ', 'ขวด', '1 ขวด', '12.84'),
(901, '1000500', 'Hyoscine-n-butyl. 5 mg/5 ml syr-30 ML', 'ขวด', '1 ขวด', '13'),
(902, '1000510', 'Eurofer Iron (III) Hydroxide Polymaltose Complex 10mg/ml-60ml', 'ขวด', '1 ขวด', '59'),
(903, '1000520', 'Milk of Magnesia (MOM)  susp - 60 mL  ', 'ขวด', '1 ขวด', '8.56'),
(904, '1000530', 'Mixt.Carminative- 180 mL                ', 'ขวด', '1 ขวด', '16.05'),
(905, '1000540', 'Opium Glycerhiza mixt (M.Tussis/Brown mixture)- 60 mL  ', 'ขวด', '1 ขวด', '17'),
(906, '1000550', 'Oral rehydration salts (ORS) 5.09 gm', 'กล่อง', '100 ซอง/กล่อง', '180'),
(907, '1000560', 'Oral rehydration salts (ORS-ส้ม) 4.201 gm ', 'กล่อง', '100 ซอง/กล่อง', '142'),
(908, '1000570', 'Paracetamol 120 mg/5 mL syr-60 mL     ', 'ขวด', '1 ขวด', '12'),
(909, '1000580', 'Co-trimoxazole susp- 60 mL Sulfamethoxazole 200 mg+ Trimethoprim40 mg/ 5 mL ', 'ขวด', '1 ขวด', '12'),
(910, '1000590', 'Dimenhydrinate 50 mg/1 mL inj', 'กล่อง', '10 แอมพูล/กล่อง', '50'),
(911, '1000600', 'Hyoscine-N-butylbromide 20 mg/1 mL inj', 'แอมพลู', '1 แอมพลู', '10.25'),
(912, '1000610', 'Lidocaine hydrochloride 2% inj- 20 mL          ', 'ขวด', '1 ขวด', '21.57'),
(913, '1000620', 'Metocloplamide HCl 10 mg/2 mL inj      ', 'แอมพลู', '1 แอมพลู', '5.35'),
(914, '1000630', 'Tetanus vaccine (Tetanus toxoid) 0.5 mL inj ', 'แอมพลู', '1 แอมพลู', '30'),
(915, '1000640', 'Sodium chloride 0.9% inj- 5 mL ampule', 'แอมพลู', '1 แอมพลู', '4.9'),
(916, '1000650', 'Water for injection-10mL plastic ampule', 'แอมพลู', '1 แอมพลู', '8'),
(917, '1000660', 'Hista-oph  Antazoline HCl 0.05%+ Tetrahydrozoline HCl 0.04% eye drop- 10 mL', 'ขวด', '1 ขวด', '32'),
(918, '1000670', 'Chloramphenicol 0.5% eye drop-10 mL    ', 'ขวด', '1 ขวด', '23.5'),
(919, '1000680', 'Chloramphenicol 1.0% ear drop-10 mL', 'ขวด', '1 ขวด', '25.5'),
(920, '1000690', 'Clotrimazole vaginal 100 mg tab  ', 'เม็ด', '6 เม็ด/กล่อง', '15.9'),
(921, '1000700', 'Lubricating gel- 50 gm   ', 'หลอด', '1หลอด', '33'),
(922, '1000710', 'Alcohol 70%- 450 mL', 'ขวด', '1 ขวด', '27.82'),
(923, '1000720', 'Alcohol 70%- 60 mL', 'ขวด', '1 ขวด', '7.49'),
(924, '1000730', 'Alcohol 95%- 450 mL (แบ่งบรรจุ)', 'ขวด', '1 ขวด', '120'),
(925, '1000740', 'Aromatic ammonia spirit-30 mL', 'ขวด', '1 ขวด', '12'),
(926, '1000750', 'Benzoic+Salicylic acid (Withfield ointment)', 'หลอด', '1หลอด', '12'),
(927, '1000760', 'Benzyl benzoate 25% emulsion- 60 mL         ', 'ขวด', '1 ขวด', '19'),
(928, '1000770', 'Betamethasone val. 0.1% cream- 5 g', 'หลอด', '1 หลอด', '8.56'),
(929, '1000780', 'Calamine lotion- 60 mL', 'ขวด', '1 ขวด', '14.98'),
(930, '1000790', 'Chlorhexidine glu.scrub 4%- 450 mL ', 'ขวด', '1 ขวด', '115'),
(931, '1000800', 'Clotrimazole 1% cream- 15 gm', 'หลอด', '1 หลอด', '30'),
(932, '1000810', 'Glycerin borax-10 mL - 15 ml', 'ขวด', '1 ขวด', '15'),
(933, '1000820', 'Hydrogen peroxide 6%- 450 mL          ', 'ขวด', '1 ขวด', '25.78'),
(934, '1000830', 'Iodine Povidine scrub 7.5%- 450 mL', 'ขวด', '1 ขวด', '115'),
(935, '1000840', 'Methylsalicylate 15% cream- 15 g ระกำ', 'หลอด', '1 หลอด', '12.84'),
(936, '1000850', 'Povidone-Iodine sol 10%- 30 mL', 'ขวด', '1 ขวด', '16.23'),
(937, '1000860', 'Povidone-Iodine sol 10%- 450 mL', 'ขวด', '1 ขวด', '120'),
(938, '1000870', 'Silver sulfadiazine 1% cream- 25 gm  ', 'หลอด', '1 หลอด', '29'),
(939, '1000880', 'Sodium chloride for irrigation-100 mL ', 'ขวด', '1 ขวด', '24'),
(940, '1000890', 'Sodium chloride for irrigation-1000 mL ', 'ขวด', '1 ขวด', '29.4'),
(941, '1000900', 'Triamcinolone acetonide 0.02% cream-5gm                       ', 'หลอด', '1 หลอด', '12.98'),
(942, '1000910', 'Triamcinolone acetonide 0.1% cream-5 gm                      ', 'หลอด', '1 หลอด', '8.56'),
(943, '1000920', 'Triamcinolone acetonide 0.1% in oral base', 'หลอด', '1 หลอด', '4.7'),
(944, '1000930', 'Zinc oxide paste- 5 gm         ', 'หลอด', '1 หลอด', '14'),
(945, '1000940', 'Adrenaline (Epinephrine) inj. 1 mg/1ml', 'แอมพลู', '1 แอมพลู', '6'),
(946, '1000950', 'Chlorpheniramine maleate 10 mg/1 mL inj', 'แอมพลู', '1 แอมพลู', '2.24'),
(947, '1000960', 'D-50-W inj. 50 ml (Glucose 50%)', 'ขวด', '1 ขวด', '25'),
(948, '1000970', 'D-5-S inj- 1000 mL', 'ขวด', '1 ขวด', '34'),
(949, '1000980', 'D-5-W inj- 1000 mL                  ', 'ขวด', '1 ขวด', '47'),
(950, '1000990', 'Dexamethasone phosphate 4 mg/1 mL inj', 'Amp', '1 amp', '4.2'),
(951, '1001000', 'Isosorbide dinitrate 5 mg sublingual tab', ' แผง', '10 เม็ด/แผง', '11'),
(952, '1001010', 'Sodium chloride 0.9% inj- 1000 mL', 'ขวด', '1 ขวด', '33'),
(953, '1001020', 'ขมิ้นชัน 500 mg cap', 'กล่อง', '500 เม็ด/กล่อง', '270'),
(954, '1001030', 'ครีมไพล 14% cream- 30 g', 'หลอด', '1 หลอด', '34.24'),
(955, '1001040', 'พญายอ (ครีม) cream- 5 gm ', 'หลอด', '1 หลอด', '42.8'),
(956, '1001050', 'ฟ้าทะลายโจร 250-500 mg cap', 'กล่อง', '500 เม็ด/กล่อง', '77.04'),
(957, '1001060', 'ยาแก้ไอมะขามป้อม 120 ml', 'ขวด', '1 ขวด', '16.05'),
(958, '1001070', 'ยาเถาวัลย์เปรียง 600 มก.', 'กล่อง', '500 เม็ด/กล่อง', '330'),
(959, '1001080', 'ยาเพชรสังฒาต 400 cap', 'กล่อง', '500 เม็ด/กล่อง', '330'),
(960, '1001090', 'ยามะขามแขก 250 มก. Tab', 'กล่อง', '500 เม็ด/กล่อง', '275'),
(961, '1001100', 'ยาเม็ดอมมะแว้ง (ประสะมะแว้ง) 20ซอง*20 เม็ด', 'กล่อง', '400 เม็ด/กล่อง', '88.8'),
(962, '1001110', 'ยารางจืด (ชารางจืด)', ' ถุง', '20 ซอง/ถุง', '52'),
(963, '1001120', 'ยาหอมเทพจิตร', 'กล่อง', '400 ซอง/กล่อง', '180'),
(964, '1001130', 'ลูกประคบสมุนไพร ยาประคบ', 'ลูก', '1 ลูก', '47'),
(965, '1001140', 'ยาคุมแบบเม็ด Levonorgestrel 150 mcg+ Ethinyl estradiol 30 mcg tab', 'กล่อง', '50 แผง/กล่อง', '385'),
(966, '1001150', 'ยาคุมฉีด Medroxyprogesterone  acetate 150 mg/3 mL inj- vial', 'Vial', '1 Vial', '11'),
(967, '1001160', 'Benzocaine 20% gel- 30 gm', 'หลอด', '1 หลอด', '100'),
(968, '1001170', 'Erythrocin 6% dye solution- 10 mL ', 'ขวด', '1 ขวด', '24'),
(969, '1001180', 'Chlorhexidine gluconate 0.12% mouth wash-240 mL', 'ขวด', '1 ขวด', '35'),
(970, '1001190', 'Mepivacaine HCl 2%+ Epi 1:100,000 in Catridge inj- 1.8 mL', 'หลอด', '1 หลอด', '13'),
(971, '1001200', 'Mepivacaine hydrochloride 3% inj- 1.8 mL', 'หลอด', '2 หลอด', '19'),
(973, '1001220', 'Special mouthwash (Dobell)- 240 mL', 'ขวด', '1 ขวด', '26');

-- --------------------------------------------------------

--
-- Table structure for table `po`
--

CREATE TABLE `po` (
  `record_number` int NOT NULL,
  `po_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` datetime NOT NULL,
  `dept_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `working_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `format_item_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `packing_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_value` decimal(15,2) DEFAULT NULL,
  `status` enum('อนุมัติ','รออนุมัติ','ยกเลิกใบเบิก') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'รออนุมัติ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po`
--

INSERT INTO `po` (`record_number`, `po_number`, `date`, `dept_id`, `working_code`, `item_code`, `format_item_code`, `quantity`, `price`, `remarks`, `packing_size`, `total_value`, `status`) VALUES
(1, 'A01', '2024-11-18 15:25:57', 'ทดสอบ admin', '1000090', 'Cetirizine 10 mg tab', 'แผง', 10, 2.50, '', '10 เม็ด/แผง', 25.00, 'อนุมัติ'),
(2, 'A01', '2024-11-18 15:25:57', 'ทดสอบ admin', '1000570', 'Paracetamol 120 mg/5 mL syr-60 mL', 'ขวด', 10, 12.00, '', '1 ขวด', 120.00, 'อนุมัติ'),
(3, 'A02', '2024-11-18 15:26:21', 'ทดสอบ admin', '1001070', 'ยาเถาวัลย์เปรียง 600 มก.', 'กล่อง', 10, 330.00, '', '500 เม็ด/กล่อง', 3300.00, 'ยกเลิกใบเบิก'),
(4, 'A03', '2024-11-18 15:26:58', 'ทดสอบ admin', '1001110', 'ยารางจืด (ชารางจืด)', 'ถุง', 10, 52.00, '', '20 ซอง/ถุง', 520.00, 'รออนุมัติ');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username_account` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hospital_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hospital_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `responsible_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hospital_contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username_account`, `password`, `hospital_code`, `hospital_name`, `responsible_person`, `contact_number`, `hospital_contact_number`, `role`) VALUES
(1, 'admin', '$2y$10$.D11UypRw4KenVJCXfmHsuwwAslZtxQrKPuUBR4cUaGef6jK.FEPK', '', 'ทดสอบ admin', '', '', '', 'user'),
(4, 'chaiya', '$2y$10$ALDqxNnMHOwqoyR5iMvD0OfWLXTSjv9O5NkPI7zSfUMpozqMz0k1W', '', 'ทดสอบ chaiya', '', '', '', 'user'),
(17, 'chaiya1', '$2y$10$plNsX1MNFYU6xlLEFh3PK.t.rNQ1hh3Zfn6Ba9Eqj5RbrvO91j5Ti', '06104', 'รพ.สต.มะกอก', 'ไชยา', '0997834912', '053005189', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drug_list`
--
ALTER TABLE `drug_list`
  ADD PRIMARY KEY (`id_code`);

--
-- Indexes for table `po`
--
ALTER TABLE `po`
  ADD PRIMARY KEY (`record_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drug_list`
--
ALTER TABLE `drug_list`
  MODIFY `id_code` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=974;

--
-- AUTO_INCREMENT for table `po`
--
ALTER TABLE `po`
  MODIFY `record_number` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
