<?php
session_start();
require_once '../config.php'; // Connect to your database

// Debug session (ลบออกเมื่อใช้งานจริง)
// echo '<pre>'; print_r($_SESSION); echo '</pre>';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Flash message (PRG)
$flashMessage = null;
$flashType = null; // 'success', 'error', 'info'
if (isset($_SESSION['flash'])) {
    $flashMessage = $_SESSION['flash']['message'] ?? null;
    $flashType = $_SESSION['flash']['type'] ?? 'success';
    unset($_SESSION['flash']);
}


// Handle Add Drug
if (isset($_POST['add_drug'])) {
    $working_code = $_POST['working_code'];
    $name_item_code = $_POST['name_item_code'];
    $format_item = $_POST['format_item'];
    $packing_code = $_POST['packing_code'];
    $price_unit_code = $_POST['price_unit_code'];
    try {
        $stmt = $con->prepare("INSERT INTO drug_list (working_code, name_item_code, format_item, packing_code, price_unit_code) VALUES (?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$working_code, $name_item_code, $format_item, $packing_code, $price_unit_code]);
        if ($ok) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'เพิ่มรายการสำเร็จ'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ไม่สามารถเพิ่มรายการได้'];
        }
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มรายการ'];
    }
    header('Location: admin_drug_list.php');
    exit();
}

// Handle Edit Drug
if (isset($_POST['edit_drug'])) {
    $id_code = $_POST['id_code'];
    $working_code = $_POST['working_code'];
    $name_item_code = $_POST['name_item_code'];
    $format_item = $_POST['format_item'];
    $packing_code = $_POST['packing_code'];
    $price_unit_code = $_POST['price_unit_code'];
    try {
        $stmt = $con->prepare("UPDATE drug_list SET working_code=?, name_item_code=?, format_item=?, packing_code=?, price_unit_code=? WHERE id_code=?");
        $ok = $stmt->execute([$working_code, $name_item_code, $format_item, $packing_code, $price_unit_code, $id_code]);
        if ($ok && $stmt->rowCount() > 0) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'บันทึกการแก้ไขสำเร็จ'];
        } else {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'ไม่พบรายการหรือไม่มีการเปลี่ยนแปลง'];
        }
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขรายการ'];
    }
    $redirect = 'admin_drug_list.php';
    if (isset($_GET['edit_id'])) { $redirect .= '?edit_id=' . urlencode($_GET['edit_id']); }
    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $redirect .= (strpos($redirect, '?') !== false ? '&' : '?') . 'search=' . urlencode($_GET['search']);
    }
    header('Location: ' . $redirect);
    exit();
}

// Handle Delete Drug
if (isset($_POST['delete_drug'])) {
    $id_code = $_POST['id_code'];
    try {
        $stmt = $con->prepare("DELETE FROM drug_list WHERE id_code=?");
        $ok = $stmt->execute([$id_code]);
        if ($ok && $stmt->rowCount() > 0) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'ลบรายการสำเร็จ'];
        } else {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'ไม่พบรายการที่จะลบ'];
        }
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบรายการ'];
    }
    $redirect = 'admin_drug_list.php';
    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $redirect .= '?search=' . urlencode($_GET['search']);
    }
    header('Location: ' . $redirect);
    exit();
}

// Search filter (require at least 3 characters to filter)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTooShort = false;
$strlen = function($s) { return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s); };

if ($search !== '' && $strlen($search) >= 3) {
    $stmt = $con->prepare("SELECT * FROM drug_list WHERE name_item_code LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $drugs = $stmt->fetchAll();
} else {
    if ($search !== '' && $strlen($search) < 3) { $searchTooShort = true; }
    $stmt = $con->query("SELECT * FROM drug_list");
    $drugs = $stmt->fetchAll();
}

// ดึงข้อมูลรายการที่เลือกแก้ไข
$editDrug = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $con->prepare("SELECT * FROM drug_list WHERE id_code=?");
    $stmt->execute([$edit_id]);
    $editDrug = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Drug List Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen">
    <?php include_once __DIR__ . '/nav.php'; ?>

    <?php if (!empty($flashMessage)): ?>
    <div class="fixed top-4 right-4 z-50">
        <div class="auto-hide mb-2 px-4 py-3 rounded shadow border <?php echo $flashType === 'success' ? 'bg-green-100 border-green-300 text-green-800' : ($flashType === 'error' ? 'bg-red-100 border-red-300 text-red-800' : 'bg-blue-100 border-blue-300 text-blue-800'); ?>">
            <?php echo htmlspecialchars($flashMessage); ?>
        </div>
    </div>
    <script>
        setTimeout(function(){
            document.querySelectorAll('.auto-hide').forEach(function(el){ el.remove(); });
        }, 2500);
    </script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Sidebar: Drug List -->
        <aside class="md:w-1/3 bg-white shadow-xl rounded-lg p-6 flex flex-col m-4 md:m-8">
                <div id="searchHelper">
                <?php if (!empty($search) && isset($searchTooShort) && $searchTooShort): ?>
                    <p class="text-xs text-red-600 mt-1">พิมพ์อย่างน้อย 3 ตัวอักษรเพื่อค้นหา</p>
                <?php endif; ?>
                </div>

            <h2 class="text-2xl font-extrabold mb-6 text-blue-700 tracking-tight">รายการยา</h2>
            <form method="get" class="mb-4" id="drugSearchForm">
                <input type="text" name="search" id="drugSearchInput" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาชื่อยา..." class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                <?php if (isset($_GET['edit_id'])): ?>
                    <input type="hidden" name="edit_id" value="<?php echo intval($_GET['edit_id']); ?>">
                <?php endif; ?>
            </form>
            <script>
                (function(){
                    var input = document.getElementById('drugSearchInput');
                    if (!input) return;
                    var timer = null;
                    var isComposing = false; // ป้องกัน fetch ระหว่างพิมพ์ด้วย IME

                    function renderFromDoc(doc){
                        var newList = doc.querySelector('#drugListContainer');
                        if (newList) {
                            var currentList = document.getElementById('drugListContainer');
                            if (currentList) currentList.replaceWith(newList);
                        }
                        var existingHelper = document.getElementById('searchHelper');
                        if (existingHelper) existingHelper.remove();
                        var newHelper = doc.querySelector('#searchHelper');
                        if (newHelper) {
                            var afterInput = document.getElementById('drugSearchInput');
                            if (afterInput && afterInput.parentNode) {
                                afterInput.insertAdjacentElement('afterend', newHelper);
                            }
                        }
                    }
                    function performFetch(){
                        var val = input.value.trim();
                        var url = new URL(window.location.href);
                        var params = url.searchParams;
                        if (val.length === 0) { params.delete('search'); } else { params.set('search', val); }
                        // คงค่า edit_id ถ้ามี
                        var editHidden = document.querySelector('#drugSearchForm input[name="edit_id"]');
                        if (editHidden && editHidden.value) { params.set('edit_id', editHidden.value); }
                        var fetchUrl = url.pathname + '?' + params.toString();
                        fetch(fetchUrl, { headers: { 'X-Requested-With': 'fetch' }})
                            .then(function(r){ return r.text(); })
                            .then(function(html){
                                var parser = new DOMParser();
                                var doc = parser.parseFromString(html, 'text/html');
                                renderFromDoc(doc);
                                window.history.replaceState(null, '', fetchUrl);
                            })
                            .catch(function(err){ console.warn('Fetch search failed', err); });
                    }

                    function scheduleFetch(){
                        if (timer) clearTimeout(timer);
                        timer = setTimeout(function(){
                            if (isComposing) return; // ยังพิมพ์ค้างอยู่
                            var val = input.value.trim();
                            // ทำงานเมื่อเคลียร์ (แสดงทั้งหมด) หรือพิมพ์ครบ >= 3 ตัวอักษรเท่านั้น
                            if (val.length === 0 || val.length >= 3) {
                                performFetch();
                            }
                        }, 600); // หน่วงเวลาเพิ่มให้พิมพ์ต่อได้
                    }

                    input.addEventListener('compositionstart', function(){ isComposing = true; });
                    input.addEventListener('compositionend', function(){ isComposing = false; scheduleFetch(); });
                    input.addEventListener('input', scheduleFetch);
                })();
            </script>
            <script>
                // ป้องกันการ submit ด้วยปุ่ม Enter ในฟอร์มค้นหา ให้ทำงานด้วย debounce เท่านั้น
                document.addEventListener('DOMContentLoaded', function(){
                    var form = document.getElementById('drugSearchForm');
                    if (!form) return;
                    form.addEventListener('keydown', function(e){
                        if (e.key === 'Enter') { e.preventDefault(); }
                    });
                });
            </script>

            <div id="drugListContainer">
                <div class="overflow-y-auto" style="max-height: 500px;">
                    <ul>
                        <?php foreach ($drugs as $row): ?>
                            <li class="mb-2">
                                <a href="?edit_id=<?php echo $row['id_code']; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
                                   class="block px-4 py-3 rounded-lg shadow-sm hover:bg-blue-100 transition <?php echo (isset($_GET['edit_id']) && $_GET['edit_id'] == $row['id_code']) ? 'bg-blue-200 font-bold border border-blue-400' : 'bg-white'; ?>">
                                    <span class="text-lg"><?php echo htmlspecialchars($row['name_item_code']); ?></span>
                                    <span class="text-xs text-gray-500 ml-2"><?php echo htmlspecialchars($row['working_code']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <hr class="my-6">
            <a href="admin_drug_list.php" class="block text-center text-blue-600 hover:underline font-semibold">+ เพิ่มรายการใหม่</a>
        </aside>
        <!-- Main Content: Add/Edit/Delete Form -->
        <main class="flex-1 flex justify-center items-center p-4 md:p-8">
            <div class="w-full max-w-xl">
                <?php if ($editDrug): ?>
                    <div class="bg-white rounded-xl shadow-xl p-8">
                        <h2 class="text-2xl font-bold mb-6 text-blue-700">แก้ไข/ลบรายการยา</h2>
                        <form method="post" class="space-y-4">
                            <input type="hidden" name="id_code" value="<?php echo $editDrug['id_code']; ?>">
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Working Code</label>
                                <input type="text" name="working_code" value="<?php echo htmlspecialchars($editDrug['working_code']); ?>" class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Name Item Code</label>
                                <input type="text" name="name_item_code" value="<?php echo htmlspecialchars($editDrug['name_item_code']); ?>" class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Format Item</label>
                                <input type="text" name="format_item" value="<?php echo htmlspecialchars($editDrug['format_item']); ?>" class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Packing Code</label>
                                <input type="text" name="packing_code" value="<?php echo htmlspecialchars($editDrug['packing_code']); ?>" class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Price Unit Code</label>
                                <input type="text" name="price_unit_code" value="<?php echo htmlspecialchars($editDrug['price_unit_code']); ?>" class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div class="flex gap-2 mt-6">
                                <button type="submit" name="edit_drug" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600 transition">บันทึกการแก้ไข</button>
                                <button type="submit" name="delete_drug" class="bg-red-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-600 transition" onclick="return confirm('ลบรายการนี้?');">ลบรายการ</button>
                                <a href="admin_drug_list.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">กลับ</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-xl p-8">
                        <h2 class="text-2xl font-bold mb-6 text-blue-700">เพิ่มรายการยาใหม่</h2>
                        <form method="post" class="space-y-4">
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Working Code</label>
                                <input type="text" name="working_code" placeholder="Working Code" required class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Name Item Code</label>
                                <input type="text" name="name_item_code" placeholder="Name Item Code" required class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Format Item</label>
                                <input type="text" name="format_item" placeholder="Format Item" required class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Packing Code</label>
                                <input type="text" name="packing_code" placeholder="Packing Code" required class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-medium">Price Unit Code</label>
                                <input type="text" name="price_unit_code" placeholder="Price Unit Code" required class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            </div>
                            <button type="submit" name="add_drug" class="bg-green-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-600 transition">เพิ่มรายการ</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>