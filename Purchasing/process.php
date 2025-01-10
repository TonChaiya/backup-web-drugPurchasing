<?php
include '../config.php';

// รับค่าจากฟอร์ม
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Handle save operation
$notification = '';
$duplicateEntries = []; // เก็บรายการข้อมูลที่ซ้ำกัน
if (isset($_POST['save'])) {
    try {
        // ดึงข้อมูลที่จะบันทึก
        $query = "SELECT 
                    working_code, 
                    item_code, 
                    format_item_code, 
                    SUM(quantity) as total_quantity, 
                    price, 
                    (price * SUM(quantity)) AS total_value, 
                    remarks, 
                    packing_size, 
                    status 
                  FROM po";
        if ($status && $status !== 'All') {
            $query .= " WHERE status = :status";
        }
        $query .= " GROUP BY working_code, item_code, format_item_code, price, remarks, packing_size, status";
        $query .= " ORDER BY working_code";
        $stmt = $con->prepare($query);
        if ($status && $status !== 'All') {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ตรวจสอบข้อมูลที่มีอยู่ก่อนการบันทึก
        $checkQuery = "SELECT purchase_status FROM processed WHERE working_code = :working_code AND item_code = :item_code AND format_item_code = :format_item_code";
        $checkStmt = $con->prepare($checkQuery);

        foreach ($data as $row) {
            $checkStmt->execute([
                ':working_code' => $row['working_code'],
                ':item_code' => $row['item_code'],
                ':format_item_code' => $row['format_item_code']
            ]);
            $existingRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRow) {
                $row['purchase_status'] = $existingRow['purchase_status'];
                $duplicateEntries[] = $row;
            }
        }

        if (count($duplicateEntries) > 0) {
            session_start();
            $_SESSION['duplicate_entries'] = $duplicateEntries;
            $notification = 'Swal.fire({
                title: "Warning",
                text: "Some data already exists and was not saved.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "OK",
                cancelButtonText: "Show Duplicates"
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    window.open("duplicates.php", "_blank");
                }
            });';
        } else {
            // บันทึกข้อมูลใหม่
            $insertQuery = "INSERT INTO processed 
                            (working_code, item_code, format_item_code, total_quantity, price, remarks, packing_size, total_value, status, purchase_status)
                            VALUES 
                            (:working_code, :item_code, :format_item_code, :total_quantity, :price, :remarks, :packing_size, :total_value, :status, :purchase_status)";
            $insertStmt = $con->prepare($insertQuery);

            foreach ($data as $row) {
                $statusValue = in_array($row['status'], ['อนุมัติ', 'รออนุมัติ', 'ยกเลิกใบเบิก', 'Completed']) ? $row['status'] : 'รออนุมัติ';
                $purchaseStatus = isset($_POST['purchase_status'][$row['working_code']]) ? $_POST['purchase_status'][$row['working_code']] : 'GPO';

                $insertStmt->execute([
                    ':working_code' => $row['working_code'],
                    ':item_code' => $row['item_code'],
                    ':format_item_code' => $row['format_item_code'],
                    ':total_quantity' => $row['total_quantity'],
                    ':price' => $row['price'],
                    ':remarks' => $row['remarks'],
                    ':packing_size' => $row['packing_size'],
                    ':total_value' => $row['total_value'],
                    ':status' => $statusValue,
                    ':purchase_status' => $purchaseStatus
                ]);
            }

            $notification = 'Swal.fire("Success", "Data saved successfully!", "success");';
        }
    } catch (PDOException $e) {
        $notification = 'Swal.fire("Error", "Error: ' . $e->getMessage() . '", "error");';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function updateBackgroundColor(selectElement) {
            if (selectElement.value === "จัดซื้อบริษัท") {
                selectElement.style.backgroundColor = "#fbbf24"; // Amber
            } else if (selectElement.value === "GPO") {
                selectElement.style.backgroundColor = "#34d399"; // Green
            } else {
                selectElement.style.backgroundColor = ""; // Default
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-4 rounded shadow-md w-full max-w-6xl">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-600">รายการยา</h1>
        
        <?php
        try {
            $query = "SELECT 
                        working_code, 
                        item_code, 
                        format_item_code, 
                        SUM(quantity) as total_quantity, 
                        price, 
                        (price * SUM(quantity)) AS total_value, 
                        remarks, 
                        packing_size, 
                        status 
                      FROM po";
            if ($status && $status !== 'All') {
                $query .= " WHERE status = :status";
            }
            $query .= " GROUP BY working_code, item_code, format_item_code, price, remarks, packing_size, status";
            $query .= " ORDER BY working_code";

            $stmt = $con->prepare($query);
            if ($status && $status !== 'All') {
                $stmt->bindParam(':status', $status);
            }

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $totalValue = 0;
                echo '<form method="post">';
                echo '<div class="overflow-x-auto">';
                echo '<table class="min-w-full bg-white border text-xs">';
                echo '<thead class="bg-blue-500 text-white">';
                echo '<tr>';
                echo '<th class="py-1 px-2 border-b">ลำดับ</th>';
                echo '<th class="py-1 px-2 border-b">Working Code</th>';
                echo '<th class="py-1 px-2 border-b">Item Code</th>';
                echo '<th class="py-1 px-2 border-b">Format Item Code</th>';
                echo '<th class="py-1 px-2 border-b">Total Quantity</th>';
                echo '<th class="py-1 px-2 border-b">Price</th>';
                echo '<th class="py-1 px-2 border-b">Remarks</th>';
                echo '<th class="py-1 px-2 border-b">Packing Size</th>';
                echo '<th class="py-1 px-2 border-b">Total Value</th>';
                echo '<th class="py-1 px-2 border-b">Status</th>';
                echo '<th class="py-1 px-2 border-b">Purchase Status</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                $index = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $totalValue += $row['total_value'];
                    $rowClass = $index % 2 == 0 ? 'bg-gray-100' : 'bg-white';
                    echo '<tr class="' . $rowClass . '">';
                    echo '<td class="py-1 px-2 border-b">' . $index++ . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . htmlspecialchars($row['working_code']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . htmlspecialchars($row['item_code']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . htmlspecialchars($row['format_item_code']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . number_format($row['total_quantity']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . number_format($row['price'], 2) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . htmlspecialchars($row['remarks']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . htmlspecialchars($row['packing_size']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . number_format($row['total_value'], 2) . '</td>';
                    echo '<td class="py-1 px-2 border-b">' . htmlspecialchars($row['status']) . '</td>';
                    echo '<td class="py-1 px-2 border-b">';
                    echo '<select name="purchase_status[' . htmlspecialchars($row['working_code']) . ']" class="mt-1 block w-full py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-xs" onchange="updateBackgroundColor(this)">';
                    echo '<option value="จัดซื้อบริษัท">จัดซื้อบริษัท</option>';
                    echo '<option value="GPO">GPO</option>';
                    echo '</select>';
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';
                echo '<div class="mt-4 text-right">';
                echo '<strong>Total Value: </strong>' . number_format($totalValue, 2);
                echo '</div>';
                echo '<div class="mt-4 text-right space-x-2">';
                echo '<a href="indexPurchasing.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">กลับหน้าหลัก</a>';
                echo '<input type="hidden" name="status" value="' . htmlspecialchars($status) . '">';
                echo '<button type="submit" name="save" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">บันทึก</button>';
                echo '</div>';
                echo '</form>';
            } else {
                echo '<p class="text-center">ไม่พบข้อมูล</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="text-center text-red-500">Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    <?php if ($notification) : ?>
        <script>
            <?php echo $notification; ?>
        </script>
    <?php endif; ?>
</body>
</html>
