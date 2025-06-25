<?php
session_start();
$duplicateEntries = isset($_SESSION['duplicate_entries']) ? $_SESSION['duplicate_entries'] : [];

// Fetch purchase_status from the processed table using PDO
$processedEntries = [];
$totalValue = 0; // ตัวแปรสำหรับเก็บมูลค่ารวมทั้งหมด
if (!empty($duplicateEntries)) {
    include '../config.php'; // ใช้การเชื่อมต่อฐานข้อมูลจาก config.php

    foreach ($duplicateEntries as $entry) {
        $stmt = $con->prepare("SELECT purchase_status FROM processed WHERE working_code = :working_code AND item_code = :item_code AND format_item_code = :format_item_code AND status = :status");
        $stmt->execute([
            ':working_code' => $entry['working_code'],
            ':item_code' => $entry['item_code'],
            ':format_item_code' => $entry['format_item_code'],
            ':status' => $entry['status']
        ]);
        $existingRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingRow) {
            $entry['purchase_status'] = $existingRow['purchase_status'];
        } else {
            $entry['purchase_status'] = 'N/A';
        }
        $totalValue += $entry['total_value']; // เพิ่มมูลค่ารวมของแต่ละรายการ
        $processedEntries[] = $entry;
    }
} else {
    $processedEntries = $duplicateEntries;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate Entries</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-4 rounded shadow-md w-full max-w-6xl">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-600">Duplicate Entries</h1>
        <?php if (!empty($processedEntries)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border text-xs">
                    <thead class="bg-red-500 text-white">
                        <tr>
                            <th class="py-1 px-2 border-b">ลำดับ</th>
                            <th class="py-1 px-2 border-b">Working Code</th>
                            <th class="py-1 px-2 border-b">Item Code</th>
                            <th class="py-1 px-2 border-b">Format Item Code</th>
                            <th class="py-1 px-2 border-b">Total Quantity</th>
                            <th class="py-1 px-2 border-b">Price</th>
                            <th class="py-1 px-2 border-b">Remarks</th>
                            <th class="py-1 px-2 border-b">Packing Size</th>
                            <th class="py-1 px-2 border-b">Total Value</th>
                            <th class="py-1 px-2 border-b">Status</th>
                            <th class="py-1 px-2 border-b">Purchase Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 1;
                        foreach ($processedEntries as $row): ?>
                            <tr class="bg-gray-100">
                                <td class="py-1 px-2 border-b"><?php echo $index++; ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['working_code']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['item_code']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['format_item_code']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo number_format($row['total_quantity']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo number_format($row['price'], 2); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['remarks']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['packing_size']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo number_format($row['total_value'], 2); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['status']); ?></td>
                                <td class="py-1 px-2 border-b"><?php echo htmlspecialchars($row['purchase_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <strong>Total Value: </strong><?php echo number_format($totalValue, 2); ?>
            </div>
        <?php else: ?>
            <p class="text-center">No duplicate entries found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
