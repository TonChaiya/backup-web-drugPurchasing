<?php
session_start();
require_once '../config.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Auth: admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Simple flash helper
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
$flashMessage = null; $flashType = null;
if (isset($_SESSION['flash'])) {
    $flashMessage = $_SESSION['flash']['message'] ?? null;
    $flashType = $_SESSION['flash']['type'] ?? 'success';
    unset($_SESSION['flash']);
}

// Generate template if requested
if (isset($_GET['action']) && $_GET['action'] === 'template') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('drug_list_template');
    // Header row
    $headers = ['working_code','name_item_code','format_item','packing_code','price_unit_code'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col.'1', $h);
        $col++;
    }
    // Example row (optional)
    $sheet->setCellValue('A2', 'D001');
    $sheet->setCellValue('B2', 'Paracetamol 500mg');
    $sheet->setCellValue('C2', 'Tablet');
    $sheet->setCellValue('D2', '10x10 Tab');
    $sheet->setCellValue('E2', 'TAB');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="drug_list_template.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$results = null; // store import summary

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    // Verify CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid CSRF token');
        header('Location: admin_drug_import.php');
        exit();
    }

    if (!isset($_FILES['xlsx']) || $_FILES['xlsx']['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'กรุณาเลือกไฟล์ Excel (.xlsx/.xls)');
        header('Location: admin_drug_import.php');
        exit();
    }

    $ext = strtolower(pathinfo($_FILES['xlsx']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx','xls'], true)) {
        set_flash('error', 'รองรับเฉพาะไฟล์ .xlsx หรือ .xls');
        header('Location: admin_drug_import.php');
        exit();
    }

    $updateExisting = isset($_POST['update_existing']) && $_POST['update_existing'] === '1';

    try {
        $tmpPath = $_FILES['xlsx']['tmp_name'];
        $reader = IOFactory::createReaderForFile($tmpPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmpPath);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $headerRow = $sheet->rangeToArray("A1:$highestColumn" . '1', null, true, true, true);
        $headerRow = $headerRow[1] ?? [];

        // Map headers (case-insensitive)
        $map = [
            'working_code' => null,
            'name_item_code' => null,
            'format_item' => null,
            'packing_code' => null,
            'price_unit_code' => null,
        ];
        foreach ($headerRow as $col => $val) {
            $key = strtolower(trim((string)$val));
            if (array_key_exists($key, $map)) {
                $map[$key] = $col;
            }
        }
        if (!$map['working_code'] || !$map['name_item_code']) {
            throw new RuntimeException('ไฟล์ต้องมีคอลัมน์ working_code และ name_item_code อยู่แถวแรก');
        }

        $insertStmt = $con->prepare(
            'INSERT INTO drug_list (working_code, name_item_code, format_item, packing_code, price_unit_code) VALUES (?, ?, ?, ?, ?)'
        );
        $updateStmt = $con->prepare(
            'UPDATE drug_list SET name_item_code = ?, format_item = ?, packing_code = ?, price_unit_code = ? WHERE working_code = ?'
        );
        $existsStmt = $con->prepare('SELECT id_code FROM drug_list WHERE working_code = ? LIMIT 1');

        $inserted = 0; $updated = 0; $skipped = 0; $errors = 0; $errorRows = [];

        $con->beginTransaction();
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray("A{$row}:$highestColumn{$row}", null, true, true, true)[$row] ?? [];

            $working_code = trim((string)($rowData[$map['working_code']] ?? ''));
            $name_item_code = trim((string)($rowData[$map['name_item_code']] ?? ''));
            $format_item = trim((string)($rowData[$map['format_item']] ?? ''));
            $packing_code = trim((string)($rowData[$map['packing_code']] ?? ''));
            $price_unit_code = trim((string)($rowData[$map['price_unit_code']] ?? ''));

            if ($working_code === '' || $name_item_code === '') {
                $skipped++;
                continue;
            }

            if ($updateExisting) {
                // If exists, update; else insert
                $existsStmt->execute([$working_code]);
                $exists = $existsStmt->fetch();
                if ($exists) {
                    $updateStmt->execute([$name_item_code, $format_item ?: null, $packing_code ?: null, $price_unit_code ?: null, $working_code]);
                    $updated += $updateStmt->rowCount();
                } else {
                    $insertStmt->execute([$working_code, $name_item_code, $format_item ?: null, $packing_code ?: null, $price_unit_code ?: null]);
                    $inserted++;
                }
            } else {
                // Always insert
                $insertStmt->execute([$working_code, $name_item_code, $format_item ?: null, $packing_code ?: null, $price_unit_code ?: null]);
                $inserted++;
            }
        }
        $con->commit();

        $results = compact('inserted','updated','skipped');
        set_flash('success', "นำเข้าข้อมูลสำเร็จ: เพิ่มใหม่ {$inserted} รายการ" . ($updateExisting ? ", อัปเดต {$updated} รายการ" : '') . ", ข้าม {$skipped} รายการ");
        header('Location: admin_drug_import.php');
        exit();
    } catch (Throwable $e) {
        if ($con->inTransaction()) { $con->rollBack(); }
        set_flash('error', 'เกิดข้อผิดพลาดในการนำเข้า: ' . $e->getMessage());
        header('Location: admin_drug_import.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นำเข้ารายการยา (Excel)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen">
    <?php include_once __DIR__ . '/nav.php'; ?>

    <?php if (!empty($flashMessage)): ?>
        <div class="fixed top-4 right-4 z-50">
            <div class="mb-2 px-4 py-3 rounded shadow border <?php echo $flashType === 'success' ? 'bg-green-100 border-green-300 text-green-800' : ($flashType === 'error' ? 'bg-red-100 border-red-300 text-red-800' : 'bg-blue-100 border-blue-300 text-blue-800'); ?>">
                <?php echo htmlspecialchars($flashMessage); ?>
            </div>
        </div>
        <script>setTimeout(function(){document.querySelectorAll('.fixed .mb-2').forEach(function(el){el.remove();});}, 2500);</script>
    <?php endif; ?>

    <div class="max-w-3xl mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h1 class="text-2xl font-bold text-blue-700 mb-4">นำเข้ารายการยา (Excel)</h1>
            <p class="text-gray-600 mb-4">รองรับไฟล์ .xlsx และ .xls โดยแถวแรกต้องเป็นคอลัมน์:
                <code class="bg-gray-100 px-2 py-1 rounded">working_code, name_item_code, format_item, packing_code, price_unit_code</code>
            </p>
            <div class="mb-6">
                <a href="?action=template" class="inline-block bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">ดาวน์โหลดเทมเพลต</a>
            </div>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">เลือกไฟล์ Excel</label>
                    <input type="file" name="xlsx" accept=".xlsx,.xls" required class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="update_existing" name="update_existing" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="update_existing" class="text-gray-700">อัปเดตรายการเดิมเมื่อพบ working_code ซ้ำ (มิฉะนั้นจะเพิ่มใหม่เสมอ)</label>
                </div>
                <div class="pt-2">
                    <button type="submit" name="import" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700">นำเข้า</button>
                    <a href="admin_drug_list.php" class="ml-2 text-blue-600 hover:underline">กลับไปจัดการรายการยา</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

