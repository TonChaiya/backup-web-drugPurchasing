<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// กำหนดค่าบทบาทผู้ใช้
$role = $_SESSION['role'] ?? 'user'; // กำหนดค่าเริ่มต้นเป็น 'user'


// ฟังก์ชันเพื่อดึงค่าจากฐานข้อมูลเดิม
function getExistingValue($con, $column, $recordId)
{
    $stmt = $con->prepare("SELECT $column FROM po WHERE record_number = :record_number");
    $stmt->bindParam(':record_number', $recordId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result[$column] ?? '';
}

// ตรวจสอบการส่งค่าเลขที่ใบเบิกจาก GET
if (isset($_GET['po_number'])) {
    $po_number = $_GET['po_number'];

    // ดึงข้อมูลใบเบิกทั้งหมดที่มีหมายเลขใบเบิกเดียวกันจากฐานข้อมูล
    try {
        $stmt = $con->prepare("SELECT record_number, working_code, item_code, format_item_code, quantity, price, remarks, packing_size, total_value, date 
                               FROM po 
                               WHERE po_number = :po_number 
                               ORDER BY date DESC");
        $stmt->bindParam(':po_number', $po_number);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$records) {
            echo '<p class="text-center text-red-500">ไม่พบข้อมูลใบเบิกนี้</p>';
            exit;
        }

        // ดึงวันที่และเลขที่ใบเบิก
        $existingDate = $records[0]['date'];
    } catch (PDOException $e) {
        echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
} else {
    echo '<p class="text-center text-red-500">ไม่พบเลขที่ใบเบิก</p>';
    exit;
}

// ดึง hospital_name ของผู้ใช้จากเซสชัน
$dept_id = $_SESSION['hospital_name'];

// ตรวจสอบการส่งข้อมูลฟอร์มสำหรับการบันทึกการแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedData = $_POST['records'] ?? [];
    $newRecords = $_POST['new_records'] ?? [];

    try {
        // อัปเดตข้อมูลที่มีอยู่ โดยไม่แก้ไขวันที่
        foreach ($updatedData as $recordId => $data) {
            $formatItemCode = !empty($data['format_item_code']) ? $data['format_item_code'] : getExistingValue($con, 'format_item_code', $recordId);
            $packingSize = !empty($data['packing_size']) ? $data['packing_size'] : getExistingValue($con, 'packing_size', $recordId);
            $totalValue = $data['quantity'] * $data['price'];

            $updateStmt = $con->prepare("UPDATE po 
                                         SET format_item_code = :format_item_code, 
                                             quantity = :quantity, 
                                             price = :price, 
                                             remarks = :remarks, 
                                             packing_size = :packing_size, 
                                             total_value = :total_value, 
                                             dept_id = :dept_id 
                                         WHERE record_number = :record_number");
            $updateStmt->bindParam(':format_item_code', $formatItemCode);
            $updateStmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $updateStmt->bindParam(':price', $data['price']);
            $updateStmt->bindParam(':remarks', $data['remarks']);
            $updateStmt->bindParam(':packing_size', $packingSize);
            $updateStmt->bindParam(':total_value', $totalValue);
            $updateStmt->bindParam(':dept_id', $dept_id);
            $updateStmt->bindParam(':record_number', $recordId);
            $updateStmt->execute();
        }

        // เพิ่มรายการใหม่ โดยใช้วันที่และหมายเลขใบเบิกเดิม
        foreach ($newRecords as $data) {
            if (!empty($data['working_code']) && !empty($data['item_code'])) {
                $totalValue = $data['quantity'] * $data['price'];
                $insertStmt = $con->prepare("INSERT INTO po (po_number, working_code, item_code, format_item_code, quantity, price, remarks, packing_size, total_value, date, dept_id) 
                                             VALUES (:po_number, :working_code, :item_code, :format_item_code, :quantity, :price, :remarks, :packing_size, :total_value, :date, :dept_id)");
                $insertStmt->bindParam(':po_number', $po_number);
                $insertStmt->bindParam(':working_code', $data['working_code']);
                $insertStmt->bindParam(':item_code', $data['item_code']);
                $insertStmt->bindParam(':format_item_code', $data['format_item_code']);
                $insertStmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
                $insertStmt->bindParam(':price', $data['price']);
                $insertStmt->bindParam(':remarks', $data['remarks']);
                $insertStmt->bindParam(':packing_size', $data['packing_size']);
                $insertStmt->bindParam(':total_value', $totalValue);
                $insertStmt->bindParam(':date', $existingDate); // ใช้วันที่เดิมจากใบเบิกเดิม
                $insertStmt->bindParam(':dept_id', $dept_id);
                $insertStmt->execute();
            }
        }

        // ตรวจสอบสิทธิ์และเปลี่ยนเส้นทางตามระดับผู้ใช้
        if ($role === 'admin') {
            header("Location: admin/admin.dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } catch (PDOException $e) {
        echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขใบเบิก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function updateTotalValue(recordId) {
            const quantityInput = document.getElementById(`quantity-${recordId}`);
            const priceInput = document.getElementById(`price-${recordId}`);
            const totalValueInput = document.getElementById(`total_value-${recordId}`);

            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;

            const totalValue = quantity * price;
            totalValueInput.value = totalValue.toFixed(2); // แสดงมูลค่าที่มีทศนิยมสองตำแหน่ง
        }

        function addNewRow() {
            const tableBody = document.getElementById('table-body');
            const newIndex = tableBody.children.length + 1;

            const newRow = `
                <tr id="new-row-${newIndex}">
                    <td class="py-2 px-4 border-b">${newIndex}</td>
                    <td class="py-2 px-4 border-b">
                        <input type="text" name="new_records[${newIndex}][working_code]" class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100" placeholder="รหัสยา" readonly>
                    </td>
                    <td class="py-2 px-4 border-b relative">
                        <input type="text" name="new_records[${newIndex}][item_code]" class="w-full border border-gray-300 rounded-lg px-2 py-1" placeholder="ชื่อยา" oninput="fetchDrugDetails(this.value, '${newIndex}')">
                        <div id="dropdown-${newIndex}" class="absolute z-10 bg-white border border-gray-300 rounded-lg w-full max-h-40 overflow-y-auto hidden"></div>
                    </td>
                    <td class="py-2 px-4 border-b">
                        <input type="text" name="new_records[${newIndex}][format_item_code]" class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100" placeholder="รูปแบบยา" readonly>
                    </td>
                    <td class="py-2 px-4 border-b">
                        <input type="number" id="quantity-new-${newIndex}" name="new_records[${newIndex}][quantity]" class="w-full border border-gray-300 rounded-lg px-2 py-1" placeholder="จำนวน" oninput="updateTotalValue('new-${newIndex}')">
                    </td>
                    <td class="py-2 px-4 border-b">
                        <input type="number" id="price-new-${newIndex}" name="new_records[${newIndex}][price]" class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100" step="0.01" placeholder="ราคา" readonly>
                    </td>
                    <td class="py-2 px-4 border-b">
                        <input type="text" name="new_records[${newIndex}][remarks]" class="w-full border border-gray-300 rounded-lg px-2 py-1" placeholder="หมายเหตุ">
                    </td>
                    <td class="py-2 px-4 border-b">
                        <input type="text" name="new_records[${newIndex}][packing_size]" class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100" placeholder="ขนาดบรรจุ" readonly>
                    </td>
                    <td class="py-2 px-4 border-b">
                        <input type="number" id="total_value-new-${newIndex}" name="new_records[${newIndex}][total_value]" readonly class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100" placeholder="มูลค่า">
                    </td>
                    <td class="py-2 px-4 border-b text-center">
                        <button type="button" onclick="removeNewRow('new-row-${newIndex}')" class="bg-red-500 text-white px-3 py-1 rounded">ลบ</button>
                    </td>
                </tr>
            `;

            tableBody.insertAdjacentHTML('beforeend', newRow);
        }

        function removeNewRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove(); // ลบแถวออกจากตาราง
            }
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center">แก้ไขใบเบิกเลขที่: <?php echo htmlspecialchars($po_number); ?></h2>
            <form method="POST">
                <div class="overflow: relative">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="py-2 px-4 border-b text-left">ลำดับที่</th>
                                <th class="py-2 px-4 border-b text-left">รหัสยา</th>
                                <th class="py-2 px-4 border-b text-left">ชื่อยา</th>
                                <th class="py-2 px-4 border-b text-left">รูปแบบ</th>
                                <th class="py-2 px-4 border-b text-left">จำนวน</th>
                                <th class="py-2 px-4 border-b text-left">ราคา</th>
                                <th class="py-2 px-4 border-b text-left">หมายเหตุ</th>
                                <th class="py-2 px-4 border-b text-left">ขนาดบรรจุ</th>
                                <th class="py-2 px-4 border-b text-left">มูลค่า</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <?php foreach ($records as $index => $record): ?>
                                <tr id="row-<?php echo $record['record_number']; ?>">
                                    <td class="py-2 px-4 border-b"><?php echo $index + 1; ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="text" value="<?php echo htmlspecialchars($record['working_code']); ?>" readonly class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="text" value="<?php echo htmlspecialchars($record['item_code']); ?>" readonly class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="text" value="<?php echo htmlspecialchars($record['format_item_code']); ?>" readonly class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="number" id="quantity-<?php echo $record['record_number']; ?>" name="records[<?php echo $record['record_number']; ?>][quantity]" value="<?php echo htmlspecialchars($record['quantity']); ?>" required class="w-full border border-gray-300 rounded-lg px-2 py-1" oninput="updateTotalValue('<?php echo $record['record_number']; ?>')">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="number" id="price-<?php echo $record['record_number']; ?>" name="records[<?php echo $record['record_number']; ?>][price]" value="<?php echo htmlspecialchars($record['price']); ?>" readonly step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="text" name="records[<?php echo $record['record_number']; ?>][remarks]" value="<?php echo htmlspecialchars($record['remarks']); ?>" class="w-full border border-gray-300 rounded-lg px-2 py-1">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="text" value="<?php echo htmlspecialchars($record['packing_size']); ?>" readonly class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="number" id="total_value-<?php echo $record['record_number']; ?>" value="<?php echo htmlspecialchars($record['total_value']); ?>" readonly class="w-full border border-gray-300 rounded-lg px-2 py-1 bg-gray-100">
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">
                                        <button type="button" onclick="deleteRecord('<?php echo $record['record_number']; ?>')" class="bg-red-500 text-white px-3 py-1 rounded">ลบ</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
                <div class="text-center mt-6">
                    <button type="button" onclick="addNewRow()" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">เพิ่มรายการใหม่</button>
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function fetchDrugDetails(itemName, rowIndex) {
            if (itemName.length >= 1) { // ดึงข้อมูลเมื่อกรอกอักษร 1-3 ตัว
                fetch('search_items.php?query=' + encodeURIComponent(itemName))
                    .then(response => response.json())
                    .then(data => {
                        const dropdown = document.getElementById(`dropdown-${rowIndex}`);
                        dropdown.innerHTML = ''; // ล้างรายการเก่า

                        if (data.length > 0) {
                            data.forEach(drug => {
                                const option = document.createElement('div');
                                option.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                                option.textContent = drug.name_item_code;
                                option.onclick = () => {
                                    document.querySelector(`input[name="new_records[${rowIndex}][item_code]"]`).value = drug.name_item_code;
                                    document.querySelector(`input[name="new_records[${rowIndex}][working_code]"]`).value = drug.working_code;
                                    document.querySelector(`input[name="new_records[${rowIndex}][format_item_code]"]`).value = drug.format_item;
                                    document.querySelector(`input[name="new_records[${rowIndex}][price]"]`).value = drug.price_unit_code;
                                    document.querySelector(`input[name="new_records[${rowIndex}][packing_size]"]`).value = drug.packing_code;
                                    dropdown.innerHTML = ''; // ล้าง Drop-down หลังเลือก
                                };
                                dropdown.appendChild(option);
                            });
                            dropdown.classList.remove('hidden');
                        } else {
                            dropdown.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                    });
            } else {
                document.getElementById(`dropdown-${rowIndex}`).classList.add('hidden');
            }
        }

        function deleteRecord(recordId) {
            if (confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?')) {
                fetch(`delete_po_item.php?record_number=${recordId}`, {
                        method: 'GET'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // ลบแถวจากตารางในหน้าเว็บ
                            const row = document.getElementById(`row-${recordId}`);
                            if (row) row.remove();
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบรายการ: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการลบรายการ');
                    });
            }
        }
    </script>


</body>

</html>