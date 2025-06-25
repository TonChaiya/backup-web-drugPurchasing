************************************************************************
หลังจาก v.7.5.1 in github มีการอัพเดต แบบไม่ได้อัพขึ้น git 6 version เล็กๆ
ปัจจุบัน ต่อเป็น github v.8 เป็นตัวที่รวม 6 version เล็กๆเข้าไว้
-เพิ่มรายงานประมวลผลหลังการจัดซื้อโดย หลังเชค list รายการหน้าจัดซื้อว่าซื้อจากที่ไหนแล้ว สามารถกดประมวลผล ตาราง po_processed ได้
-po_processed ก่อนประมวลผล ให้ทำการ copy ตาราง po ในฐานข้อมูลพร้อมข้อมูลมาก่อน 
ใช้คำสั่ง 
sql : CREATE TABLE po_processed AS
SELECT * FROM po;

แล้วต่อด้วย
ALTER TABLE po_processed
ADD purchase_status ENUM('GPO', 'จัดซื้อบริษัท');

****ก่อนถึงจะสามารถใช้ฟังชั่นนี้ได้*****
-***database*****
account ; เก็บข้อมูลผู้ใช้
drug_list	; รายการยาที่มีให้เบิก
medicine_info ; รายการคุณลักษณะอ้างอิงตามเลข working_code
po ; รายการใบเบิกแยกรายการ แต่ละหน่วยที่เบิกเข้ามา

po_processed ; ตารางที่ทำการ copy formตาราง po เพื่อไปทำการประมวลผลของการทำเอกสารจัดซื้อ ปุ่มประมวลผล
processed ; เก็บรายการรวมรายการยาทั้งหมด ทุกหน่วยเบิกว่าตัวไหนซื้อ gpo หรือที่อื่น

หมายเหตุ ตาราง Processed and po_processed ทำงานร่วมกัน โดยอัพเดตสถานะการเบิก purchase_status ของตาราง po_processed อ้างอิงจากตาราง processed
โดยอิงจากคอลัม working_code และ purchase_status ที่เหมือนกัน
************************************************************************
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