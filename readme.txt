v.6 เพิ่มการลงทะเบียนผุ้ใช้ สำหรับ admin/user แก้ไขหน้า  admin_register.php และ  register.php เพื่อเพิ่ม sweetalert2 หน้าต่างสำหรับแจ้งเตือนหลังจากกดบันทึก
	-แจ้งเตือนเมื่อ ชื่อ user รหัสสถานบริการ ชื่อสถานบริการซ้ำหรือเคยมีเเล้วในฐานข้อมูล
	-แจ้งเตือน เมื่อกดบันทึกลงฐานข้อมูลเรียบร้อยแล้ว
	-แก้ไข การบันทึกข้อมูลที่ไม่สัมพันธ์กับตาราง เช่น 
	
	hospital_code
	hospital_name
	responsible_person
	contact_number
	hospital_contact_number
	**ไม่ถูกบันทึกลงฐานข้อมูล sql**

	-แก้ไขปัญหารายงาน ที่ยกเลิกแล้ว ดึงได้เฉพาะบางหน่วยงาน ของ admin

v.5 รายงาน
	- admin\AdminReport\all_purchases_report.php รายงานทั้งหมดแยกรายการ
	- admin\AdminReport\all_combined_report.php รายงานทั้งหมด รวมรายการ
	- admin\AdminReport\admin_po_number_report.php รายงานตามใบเบิก
	- admin\AdminReport\admin_medicine_report.php รายงานตามชื่อยา
	- admin\AdminReport\admin_date_range_report.php รายงานตามช่วงวันที่
	- admin\AdminReport\admin_cancelled_report.php รายงานที่ยกเลิกแล้ว

v.4 ปรับปรุงส่วนของการ login admin
	-เพิ่มแดชบอด หน้าแอดมิน 100%สมบรูณ์ แก้ไขการปรับ สถานะ
	-เพิ่มหน้ารายงาน สำหรับแอดมิน เพิ่ม รายงานรวม  รายงานแยกรายการ
	-เพิ่มหน้ารายงานของ แอดมินต่างหากใน admin/AdminReport
	-ปรับnavbar.php แยกออกจากตัว html เพื่อง่ายต่อการแก้ไข (สำหรับหน้าแอดมินเท่านั้น)

v.3 แก้ไขหน้าแอดมินต่อจาก v.2 ที่ทำงานไม่สมบรูณ์ ให้สามารถใช้งานได้
	-แก้ไขปุ่ม จัดการรายงานในสถานะต่างๆ

v.2 เพิ่มการจัดการ server database และ phpmyadmin
	-เพิ่มคอลัม role user จัดการระดับผู้ใช้
	-เพิ่มการlog in admin เข้าสู่ หน้า dashboard admin

v.1 สร้างเว็บเบิกสำหรับ user ยังไม่มีการจัดการสำหรับแอดมิน
	-phpmyadmin ยังไม่มีตาราง role user/admin