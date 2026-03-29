<?php
session_start();
require "../db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Logic to delete a record (Deleting from orders since that is your transaction source)
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header("Location: transactions.php?msg=deleted");
    exit;
}

try {
    // We fetch from 'orders' and JOIN with 'users' and 'coffee' to get the names and calculate totals
    $query = "SELECT 
                o.id, 
                u.username, 
                c.name as item_name, 
                (o.quantity * c.price) as total_amount, 
                o.status as payment_status, 
                o.created_at as transaction_date
              FROM orders o
              JOIN users u ON o.user_id = u.id
              JOIN coffee c ON o.coffee_id = c.id
              ORDER BY o.created_at DESC";
    $transactions = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='color:white; background:red; padding:20px;'>Database Error: " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions | Caffinity Admin</title>
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
        th { display:none; }
        td {
            padding:4px 8px;
            border:none;
            font-size:0.95rem;
            color:#fff;
        }
        tbody tr td:first-child { padding-left:12px; }
        tbody tr td:last-child { padding-right:12px; }

        .status {
            padding:5px 12px;
            border-radius:50px;
            font-size:0.7rem;
            font-weight:600;
            text-transform:uppercase;
            display:inline-block;
            background:rgba(212,163,115,0.2);
            color:var(--primary-gold);
            border:1px solid rgba(212,163,115,0.5);
        }

        .btn-del {
            color:var(--danger);
            cursor:pointer;
            background:none;
            border:1px solid var(--danger);
            padding:5px 10px;
            border-radius:50px;
            transition:0.3s;
            font-weight:600;
        }
        .btn-del:hover { background:var(--danger); color:#fff; }
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
            <li class="nav-item"><a href="view_orders.php" class="nav-link"><i class="fa-solid fa-list-ul"></i> Orders</a></li>
            <li class="nav-item"><a href="transaction.php" class="nav-link active"><i class="fa-solid fa-coins"></i> Transactions</a></li>
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
            <input type="text" placeholder="Search transactions...">
        </div>

        <div class="glass-panel" style="padding:8px 20px; display:flex; align-items:center; gap:12px;">
            <span style="font-weight:600;">Admin Console</span>
            <i class="fa-solid fa-user-shield" style="color:var(--primary-gold);"></i>
        </div>
    </header>

    <main class="content">
        <h1 class="page-title">Transaction Audit</h1>
        
        <?php if (isset($_GET['msg'])): ?>
            <p style="color: #2ecc71; margin-bottom: 15px; padding: 10px 20px; background: rgba(46,204,113,0.2); border-radius: 10px; border-left: 3px solid #2ecc71;">Record deleted successfully.</p>
        <?php endif; ?>

        <div class="table-container">
            <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Coffee</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 50px; opacity: 0.5;">No transactions found in database.</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?= date('M d, Y | H:i', strtotime($t['transaction_date'])) ?></td>
                        <td><?= htmlspecialchars($t['username']) ?></td>
                        <td><?= htmlspecialchars($t['item_name']) ?></td>
                        <td style="color: var(--gold);">₱<?= number_format($t['total_amount'], 2) ?></td>
                        <td><span class="status"><?= htmlspecialchars($t['payment_status'] ?? 'Completed') ?></span></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this transaction record?')">
                                <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                                <button class="btn-del">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
    </main>
    </div> <!-- main-wrapper -->

</body>
</html>