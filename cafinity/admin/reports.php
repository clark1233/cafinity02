<?php
session_start();
require "../db.php";

// Protect page: only admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// --- 1. BULK ACTIONS LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['order_ids'])) {
    $ids = $_POST['order_ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    if (isset($_POST['bulk_hide'])) {
        $stmt = $pdo->prepare("UPDATE orders SET hidden = 1 WHERE id IN ($placeholders)");
        $stmt->execute($ids);
    } 
    elseif (isset($_POST['confirm_bulk_delete'])) {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)");
        $stmt->execute($ids);
    }
    header("Location: reports.php?status=success");
    exit;
}

// --- 2. RESTORE ALL LOGIC ---
if (isset($_POST['restore_all'])) {
    $pdo->query("UPDATE orders SET hidden = 0");
    header("Location: reports.php?status=restored");
    exit;
}

// --- 3. DATA FETCHING ---
$query = "SELECT o.*, c.name as coffee_name, c.price, u.username 
          FROM orders o 
          JOIN coffee c ON o.coffee_id = c.id 
          JOIN users u ON o.user_id = u.id 
          WHERE o.hidden = 0
          ORDER BY o.created_at DESC";
$orders = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// --- 4. CSV EXPORT LOGIC ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $filename = "Caffinity_Sales_Report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Date', 'Customer', 'Product', 'Quantity', 'Unit Price', 'Total Sale']);
    foreach ($orders as $order) {
        fputcsv($output, [$order['id'], $order['created_at'], $order['username'], $order['coffee_name'], $order['quantity'], $order['price'], ($order['price'] * $order['quantity'])]);
    }
    fclose($output);
    exit;
}

// 5. Analytics Logic
$grandTotalRevenue = 0; $todaySales = 0; $monthSales = 0; $yearSales = 0;
$today = date('Y-m-d'); $currentMonth = date('Y-m'); $currentYear = date('Y');

foreach ($orders as $order) {
    $subtotal = $order['price'] * $order['quantity'];
    $grandTotalRevenue += $subtotal;
    $orderDate = date('Y-m-d', strtotime($order['created_at']));
    $orderMonth = date('Y-m', strtotime($order['created_at']));
    $orderYear = date('Y', strtotime($order['created_at']));

    if ($orderDate == $today) $todaySales += $subtotal;
    if ($orderMonth == $currentMonth) $monthSales += $subtotal;
    if ($orderYear == $currentYear) $yearSales += $subtotal;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Reports | Cafinity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --gold: #d4a373; 
            --dark: #1a1a1a; 
            --glass: rgba(255, 255, 255, 0.1); 
            --red: #ff7675; 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
        body {
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('../bgindex.JPG') no-repeat center center fixed;
            background-size: cover; color: #fff; padding: 120px 40px 50px;
        }

        /* --- RESTORED NAVIGATION --- */
        .top-nav {
            position: fixed; top: 0; left: 0; width: 100%; padding: 15px 40px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(15px); z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .nav-left span { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        .nav-right a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .nav-right a:hover { color: var(--gold); }

        .header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--gold); }
        
        .btn { padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; cursor: pointer; border: none; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-gold { background: var(--gold); color: var(--dark); }
        .btn-gold:hover { background: #fff; transform: translateY(-2px); }
        .btn-outline { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: #fff; }
        .btn-outline:hover { border-color: var(--gold); color: var(--gold); }
        .btn-red { background: transparent; border: 1px solid var(--red); color: var(--red); }
        .btn-red:hover { background: var(--red); color: #fff; }

        /* --- Stats --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--glass); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(15px); }
        .stat-card p { font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
        .stat-card h2 { color: var(--gold); font-family: 'Playfair Display'; font-size: 1.8rem; }

        /* --- Table --- */
        .report-container { background: var(--glass); padding: 30px; border-radius: 25px; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); }
        table { width: 100%; border-collapse: collapse; }
        th { color: var(--gold); font-size: 0.75rem; text-transform: uppercase; padding: 15px; border-bottom: 2px solid rgba(212, 163, 115, 0.2); letter-spacing: 1px; }
        td { padding: 18px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
        input[type="checkbox"] { accent-color: var(--gold); transform: scale(1.2); cursor: pointer; }

        /* --- MODAL --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center; z-index: 2000;
        }
        .modal-content {
            background: var(--dark); padding: 40px; border-radius: 30px; border: 1px solid var(--gold);
            text-align: center; max-width: 400px; width: 90%;
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div class="nav-left"><span>Cafinity Admin</span></div>
        <div class="nav-right">
            <a href="dashboard.php">Dashboard</a>
            <a href="view_orders.php">Orders</a>
            <a href="manage_coffee.php">Inventory</a>
            <a href="users.php">Users</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <div class="header-flex">
        <div>
            <h1>Business Performance</h1>
            <p style="opacity: 0.6; font-size: 0.9rem;">Comprehensive Sales Analytics</p>
        </div>
        <div style="display:flex; gap: 12px;">
            <form method="POST"><button type="submit" name="restore_all" class="btn btn-outline">🔄 Restore All</button></form>
            <a href="reports.php?export=csv" class="btn btn-gold">📄 Export CSV</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><p>Today's Sales</p><h2>₱<?= number_format($todaySales, 2) ?></h2></div>
        <div class="stat-card"><p>Monthly Revenue</p><h2>₱<?= number_format($monthSales, 2) ?></h2></div>
        <div class="stat-card"><p>Annual Total</p><h2>₱<?= number_format($yearSales, 2) ?></h2></div>
        <div class="stat-card"><p>Lifetime Revenue</p><h2>₱<?= number_format($grandTotalRevenue, 2) ?></h2></div>
    </div>

    <form method="POST" id="bulkForm">
        <div class="report-container">
            <div style="margin-bottom: 25px; display: flex; gap: 12px; align-items: center;">
                <button type="submit" name="bulk_hide" class="btn btn-outline">👁️ Hide Selected</button>
                <button type="button" class="btn btn-red" onclick="triggerBulkDelete()">🗑️ Delete Selected</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Coffee Product</th>
                        <th style="text-align: right;">Total Sale</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 40px; opacity: 0.5;">No sales records found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><input type="checkbox" name="order_ids[]" value="<?= $order['id'] ?>" class="item-check"></td>
                        <td style="color: #bbb; font-size: 0.8rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><strong><?= htmlspecialchars($order['username']) ?></strong></td>
                        <td><?= htmlspecialchars($order['coffee_name']) ?> <span style="font-size: 0.7rem; opacity: 0.6;">(x<?= $order['quantity'] ?>)</span></td>
                        <td style="text-align: right; color: var(--gold); font-weight: 600;">₱<?= number_format($order['price'] * $order['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <input type="hidden" name="confirm_bulk_delete" id="deleteConfirmInput" disabled>
    </form>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <h2 style="font-family: 'Playfair Display'; color: var(--gold); margin-bottom: 15px;">Delete Records?</h2>
            <p style="color: #ccc; margin-bottom: 30px; font-size: 0.9rem;">This will permanently erase the selected sales data. This action cannot be undone.</p>
            <div style="display: flex; gap: 15px;">
                <button type="button" class="btn btn-outline" style="flex:1; justify-content:center;" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-gold" style="flex:1; justify-content:center; background: var(--red); color: white;" onclick="submitBulkDelete()">Delete Now</button>
            </div>
        </div>
    </div>

    <script>
        // Select All Toggle
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-check');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Modal Logic
        function triggerBulkDelete() {
            const selected = document.querySelectorAll('.item-check:checked');
            if (selected.length === 0) {
                alert('Please select at least one record to delete.');
                return;
            }
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function submitBulkDelete() {
            const input = document.getElementById('deleteConfirmInput');
            input.disabled = false;
            input.value = "1";
            document.getElementById('bulkForm').submit();
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('deleteModal')) closeDeleteModal();
        }
    </script>
</body>
</html>