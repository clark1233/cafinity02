<?php
session_start();
require "../db.php";

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Approve order logic (Only if status is currently Pending)
if (isset($_POST['approve_order'])) {
    $order_id = $_POST['order_id'];
    $stmt = $pdo->prepare("UPDATE orders SET status='Approved' WHERE id=? AND (status='Pending' OR status IS NULL)");
    $stmt->execute([$order_id]);
    header("Location: view_orders.php"); 
    exit;
}

// Fetch orders
$sql = "
SELECT o.id AS order_id, u.username AS customer_name, c.name AS coffee_name, 
        c.price, o.quantity, o.status, o.created_at
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN coffee c ON o.coffee_id = c.id
ORDER BY o.created_at DESC
";
$orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders | Caffinity Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gold: #d4a373;
            --glass-white: rgba(255, 255, 255, 0.15);
            --glass-dark: rgba(26, 18, 11, 0.7);
            --border-glass: rgba(255, 255, 255, 0.2);
            --text-light: #ffffff;
            --danger: #ff7675;
        }

        /* GLOBAL RESET */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }

        body {
            display:flex;
            height:100vh;
            background:linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('../bgindex.JPG') no-repeat center center fixed;
            background-size:cover;
            color:var(--text-light);
            overflow:hidden;
        }

        /* SIDEBAR */
        .sidebar {
            width:280px;
            background:var(--glass-dark);
            backdrop-filter:blur(15px);
            -webkit-backdrop-filter:blur(15px);
            display:flex;
            flex-direction:column;
            border-right:1px solid var(--border-glass);
            z-index:10;
        }

        .sidebar-header {
            padding:40px 30px;
            display:flex;
            align-items:center;
            gap:15px;
        }

        .sidebar-header img {
            width:45px;
            height:45px;
            border-radius:50%;
            border:2px solid var(--primary-gold);
        }

        .sidebar-header h2 {
            font-family:'Playfair Display', serif;
            color:var(--primary-gold);
            letter-spacing:1.5px;
        }

        .nav-menu { list-style:none; flex-grow:1; }
        .nav-item { padding:5px 20px; }

        .nav-link {
            display:flex;
            align-items:center;
            gap:15px;
            padding:15px;
            color:rgba(255,255,255,0.7);
            text-decoration:none;
            border-radius:15px;
            transition:0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background:var(--glass-white);
            color:#fff;
            transform:translateX(5px);
        }

        .nav-link.active {
            border-left:4px solid var(--primary-gold);
            box-shadow:0 4px 15px rgba(0,0,0,0.2);
        }

        .logout-btn {
            margin-top:auto;
            padding:30px;
            border-top:1px solid var(--border-glass);
        }

        .logout-btn a {
            color:var(--primary-gold);
            text-decoration:none;
            font-weight:600;
        }

        /* MAIN */
        .main-wrapper { flex-grow:1; overflow-y:auto; display:flex; flex-direction:column; }

        .top-bar {
            padding:25px 40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .glass-panel {
            background:var(--glass-white);
            backdrop-filter:blur(10px);
            -webkit-backdrop-filter:blur(10px);
            border:1px solid var(--border-glass);
            border-radius:20px;
            box-shadow:0 8px 32px rgba(0,0,0,0.3);
        }

        .search-container {
            padding:10px 20px;
            display:flex;
            align-items:center;
            width:350px;
        }

        .search-container input {
            border:none;
            outline:none;
            background:transparent;
            color:white;
            margin-left:10px;
            width:100%;
        }

        .search-container input::placeholder {
            color:rgba(255,255,255,0.6);
        }

        .content { padding:0 40px 40px; }

        .page-title {
            font-family:'Playfair Display', serif;
            font-size:2.5rem;
            margin-bottom:30px;
            text-shadow:2px 4px 10px rgba(0,0,0,0.5);
        }

        .table-container { padding:30px; width:100%; background:transparent; }
        table { width:100%; border-collapse:separate; border-spacing:0 60px; color:#fff; }
        thead { display:none; }
        tbody tr {
            background:rgba(255,255,255,0.08);
            backdrop-filter:blur(10px);
            -webkit-backdrop-filter:blur(10px);
            border-radius:20px;
            border:1px solid rgba(255,255,255,0.2);
            box-shadow:0 8px 32px rgba(0,0,0,0.2);
            transition:transform 0.3s, box-shadow 0.3s;
            display:table;
            width:100%;
        }
        tbody tr:hover {
            transform:translateY(-4px);
            box-shadow:0 12px 40px rgba(0,0,0,0.3);
        }
        th {
            display:none;
        }
        td {
            padding:4px 5px;
            border:none;
            font-size:0.95rem;
            color:#fff;
        }
        tbody tr td:first-child { padding-left:10px; }
        tbody tr td:last-child { padding-right:10px; }

        .status-badge {
            padding:5px 12px;
            border-radius:50px;
            font-size:0.7rem;
            font-weight:600;
            text-transform:uppercase;
            display:inline-block;
        }
        .status-Pending { color:#ffeaa7; background:rgba(255,234,167,0.1); border:1px solid #ffeaa7; }
        .status-Approved { color:#55efc4; background:rgba(85,239,196,0.1); border:1px solid #55efc4; }
        .status-Cancelled { color:var(--danger); background:rgba(255,118,117,0.1); border:1px solid var(--danger); }

        .btn-approve {
            background:var(--primary-gold);
            color:#1a1a1a;
            border:none;
            padding:8px 20px;
            border-radius:50px;
            font-weight:600;
            cursor:pointer;
            transition:0.3s;
            font-size:0.8rem;
        }
        .btn-approve:hover { background:#fff; transform:translateY(-2px); }
    </style>
</head>
<body>

<aside class="sidebar">
        <div class="sidebar-header">
            <img src="../indexpic.jpg" alt="Logo">
            <h2>CAFINITY</h2>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
            <li class="nav-item"><a href="manage_coffee.php" class="nav-link"><i class="fa-solid fa-mug-hot"></i> Menu</a></li>
            <li class="nav-item"><a href="view_orders.php" class="nav-link active"><i class="fa-solid fa-list-ul"></i> Orders</a></li>
            <li class="nav-item"><a href="transaction.php" class="nav-link"><i class="fa-solid fa-coins"></i> Transactions</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link"><i class="fa-solid fa-users"></i> Users</a></li>
        </ul>

        <div class="logout-btn">
            <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
    <header class="top-bar">
        <div class="glass-panel search-container">
            <i class="fa-solid fa-magnifying-glass" style="color: var(--primary-gold);"></i>
            <input type="text" placeholder="Search orders...">
        </div>

        <div class="glass-panel" style="padding:8px 20px; display:flex; align-items:center; gap:12px;">
            <span style="font-weight:600;">Admin Console</span>
            <i class="fa-solid fa-user-shield" style="color:var(--primary-gold);"></i>
        </div>
    </header>

    <main class="content">
        <h1 class="page-title">Customer Orders</h1>

        <div class="table-container">
            <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Coffee Item</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $order): 
                $current_status = $order['status'] ?: 'Pending';
            ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($order['coffee_name']); ?></td>
                <td><?php echo $order['quantity']; ?></td>
                <td style="color: var(--primary-gold); font-weight: 600;">
                    ₱<?php echo number_format($order['price'] * $order['quantity'], 2); ?>
                </td>
                <td>
                    <span class="status-badge status-<?php echo $current_status; ?>">
                        <?php echo $current_status; ?>
                    </span>
                </td>
                <td>
                    <?php if($current_status == 'Pending'): ?>
                        <form method="post">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button type="submit" name="approve_order" class="btn-approve">Approve</button>
                        </form>
                    <?php elseif($current_status == 'Cancelled'): ?>
                        <span style="color: var(--danger); font-size: 0.8rem; opacity: 0.7;">User Cancelled</span>
                    <?php else: ?>
                        <span style="color: #55efc4; font-size: 0.85rem;">✔ Confirmed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        </div>
    </main>
    </div> <!-- main-wrapper -->

</body>
</html>