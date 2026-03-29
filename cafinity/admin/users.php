<?php
session_start();
require "../db.php";

// Security check: Only allow admins
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Handle user deletion
if (isset($_POST['confirm_delete'])) {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id=?");
    $stmt->execute([$user_id]);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='customer'");
    $stmt->execute([$user_id]);
    header("Location: users.php?status=removed");
    exit;
}

// Fetch users
$stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY username ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Caffinity Admin</title>
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

        /* --- GLOBAL RESET --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            display: flex;
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                        url('../bgindex.JPG') no-repeat center center fixed;
            background-size: cover;
            color: var(--text-light);
            overflow: hidden;
        }

        /* --- SIDEBAR (MATCHED 100%) --- */
        .sidebar {
            width: 280px;
            background: var(--glass-dark);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-glass);
            z-index: 10;
        }

        .sidebar-header {
            padding: 40px 30px;
            display: flex; align-items: center; gap: 15px;
        }

        .sidebar-header img {
            width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--primary-gold);
        }

        .sidebar-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-gold);
            letter-spacing: 1.5px;
        }

        .nav-menu { list-style: none; flex-grow: 1; }
        .nav-item { padding: 5px 20px; }

        .nav-link {
            display: flex; align-items: center; gap: 15px; padding: 15px;
            color: rgba(255, 255, 255, 0.7); text-decoration: none;
            border-radius: 15px; transition: 0.3s ease;
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

        /* --- LOGOUT (FIXED: MATCHED DASHBOARD NO-SPACE STYLE) --- */
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

        /* --- MAIN WRAPPER & TOP BAR --- */
        .main-wrapper { flex-grow: 1; overflow-y: auto; display: flex; flex-direction: column; }

        .top-bar {
            padding: 25px 40px; 
            display: flex; justify-content: space-between; align-items: center;
        }

        .glass-panel {
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        /* --- SEARCH CONTAINER (FIXED: MATCHED DASHBOARD BACKGROUND) --- */
        .search-container {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            width: 350px;
        }

        .search-container input {
            border: none; outline: none; background: transparent; 
            color: white; margin-left: 10px; width: 100%;
        }

        .search-container input::placeholder { color: rgba(255,255,255,0.6); }

        /* --- CONTENT AREA --- */
        .content { padding: 0 40px 40px; }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-shadow: 2px 4px 10px rgba(0,0,0,0.5);
        }

        /* --- TABLE STYLING --- */
        .table-container { padding: 30px; width: 100%; }
        
        table { width: 100%; border-collapse: collapse; }
        
        th {
            text-align: left; padding: 15px; color: var(--primary-gold);
            text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;
            border-bottom: 2px solid var(--border-glass);
        }
        
        td { padding: 20px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.95rem; }

        .role-badge {
            padding: 5px 15px; border-radius: 50px; font-size: 0.75rem;
            font-weight: 600; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.3);
        }
        
        .role-admin { background: rgba(212, 163, 115, 0.2); color: var(--primary-gold); border-color: var(--primary-gold); }

        .btn-delete {
            background: transparent; color: var(--danger); border: 1px solid var(--danger);
            padding: 8px 18px; border-radius: 50px; cursor: pointer; transition: 0.3s;
        }
        
        .btn-delete:hover { background: var(--danger); color: #fff; }

        /* --- SCROLLBAR --- */
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
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
            <li class="nav-item"><a href="manage_coffee.php" class="nav-link"><i class="fa-solid fa-mug-hot"></i> Menu</a></li>
            <li class="nav-item"><a href="view_orders.php" class="nav-link"><i class="fa-solid fa-list-ul"></i> Orders</a></li>
            <li class="nav-item"><a href="transaction.php" class="nav-link"><i class="fa-solid fa-coins"></i> Transactions</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link active"><i class="fa-solid fa-users"></i> Users</a></li>
        </ul>

        <div class="logout-btn">
            <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="top-bar">
            <div class="glass-panel search-container">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--primary-gold);"></i>
                <input type="text" placeholder="Search accounts...">
            </div>
            
            <div class="glass-panel" style="padding: 8px 20px; display: flex; align-items: center; gap: 12px;">
                <span style="font-weight: 600;">Admin Console</span>
                <i class="fa-solid fa-user-shield" style="color: var(--primary-gold);"></i>
            </div>
        </header>

        <main class="content">
            <h1 class="page-title">User Management</h1>

            <div class="glass-panel table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td>
                                <span class="role-badge <?php echo ($user['role'] === 'admin') ? 'role-admin' : ''; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" onsubmit="return confirm('Remove this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="confirm_delete" class="btn-delete">Remove</button>
                                    </form>
                                <?php else: ?>
                                    <span style="opacity: 0.5; font-size: 0.8rem;"><i class="fa-solid fa-shield-halved"></i> Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>