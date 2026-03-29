<?php
session_start();
require "../db.php";

// Security check: Only allow admins
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Fetch total users
$users_stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$users_count = $users_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Fetch total orders
$orders_stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$orders_count = $orders_stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Fetch total revenue (sum of all orders)
$revenue_stmt = $pdo->query("SELECT SUM(c.price * o.quantity) as total_revenue FROM orders o JOIN coffee c ON o.coffee_id = c.id");
$total_revenue = $revenue_stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Cafinity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gold: #d4a373;
            --glass-white: rgba(255, 255, 255, 0.15); /* Translucent White */
            --glass-dark: rgba(26, 18, 11, 0.7);    /* Translucent Espresso */
            --border-glass: rgba(255, 255, 255, 0.2);
            --text-light: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            /* Using your original background image */
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                        url('../bgindex.JPG') no-repeat center center fixed;
            background-size: cover;
            color: var(--text-light);
            overflow: hidden;
        }

        /* --- GLASS SIDEBAR --- */
        .sidebar {
            width: 280px;
            background: var(--glass-dark);
            backdrop-filter: blur(15px); /* This creates the frost effect */
            -webkit-backdrop-filter: blur(15px);
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-glass);
            z-index: 10;
        }

        .sidebar-header {
            padding: 40px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar-header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--primary-gold);
        }

        .sidebar-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-gold);
            letter-spacing: 1.5px;
        }

        .nav-menu { list-style: none; flex-grow: 1; }
        .nav-item { padding: 5px 20px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 15px;
            transition: 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--glass-white);
            color: #fff;
            transform: translateX(5px);
        }

        .nav-link.active {
            border-left: 4px solid var(--primary-gold);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* --- MAIN CONTENT AREA --- */
        .main-wrapper {
            flex-grow: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .glass-panel {
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .search-container {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            width: 350px;
        }

        .search-container input {
            border: none;
            outline: none;
            background: transparent;
            color: white;
            margin-left: 10px;
        }

        .search-container input::placeholder { color: rgba(255,255,255,0.6); }

        .content { padding: 0 40px 40px; }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-shadow: 2px 4px 10px rgba(0,0,0,0.5);
        }

        /* --- GLASS STAT CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.4s;
        }

        .stat-card:hover {
            transform: scale(1.03);
            background: rgba(255, 255, 255, 0.25);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        /* Keep the colors from the reference image but make them glass-compatible */
        .bg-rev { background: rgba(72, 187, 120, 0.6); }
        .bg-usr { background: rgba(237, 100, 166, 0.6); }
        .bg-new { background: rgba(237, 137, 54, 0.6); }

        .stat-label { font-size: 0.8rem; color: rgba(255,255,255,0.7); text-transform: uppercase; font-weight: 600; }
        .stat-value { font-size: 1.8rem; font-weight: 700; }

        /* --- GLASS CHARTS --- */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .chart-card { padding: 25px; min-height: 200px; }

        .chart-placeholder {
            margin-top: 15px;
            height: 150px;
            border: 1px dashed rgba(255,255,255,0.3);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.4);
        }

        .logout-btn {
            margin-top: auto;
            padding: 30px;
            border-top: 1px solid var(--border-glass);
        }

        .logout-btn a {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: 600;
        }

        /* Custom Scrollbar for the Glass look */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../indexpic.jpg" alt="Logo">
            <h2>CAFINITY</h2>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item"><a href="#" class="nav-link active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
            <li class="nav-item"><a href="manage_coffee.php" class="nav-link"><i class="fa-solid fa-mug-hot"></i> Menu</a></li>
            <li class="nav-item"><a href="view_orders.php" class="nav-link"><i class="fa-solid fa-list-ul"></i> Orders</a></li>
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
                <input type="text" placeholder="Search analytics...">
            </div>
            
            <div class="glass-panel" style="padding: 8px 20px; display: flex; align-items: center; gap: 12px;">
                <span style="font-weight: 600;">Admin Console</span>
                <i class="fa-solid fa-user-shield" style="color: var(--primary-gold);"></i>
            </div>
        </header>

        <main class="content">
            <h1 class="page-title">Analytics Overview</h1>

            <div class="stats-grid">
                <div class="glass-panel stat-card">
                    <div class="icon-box bg-rev"><i class="fa-solid fa-chart-line"></i></div>
                    <div>
                        <p class="stat-label">Total Revenue</p>
                        <h2 class="stat-value">₱<?php echo number_format($total_revenue, 2); ?></h2>
                    </div>
                </div>

                <div class="glass-panel stat-card">
                    <div class="icon-box bg-usr"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <p class="stat-label">Active Users</p>
                        <h2 class="stat-value"><?php echo $users_count; ?></h2>
                    </div>
                </div>

                <div class="glass-panel stat-card">
                    <div class="icon-box bg-new"><i class="fa-solid fa-mug-saucer"></i></div>
                    <div>
                        <p class="stat-label">Total Orders</p>
                        <h2 class="stat-value"><?php echo $orders_count; ?></h2>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="glass-panel chart-card">
                    <p class="stat-label">Weekly Sales Trend</p>
                    <div class="chart-placeholder">Chart Visualization</div>
                </div>
                <div class="glass-panel chart-card">
                    <p class="stat-label">User Engagement</p>
                    <div class="chart-placeholder">Activity Map</div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>