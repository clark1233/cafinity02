<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit;
}

$show_modal = false;

// AUTO-UPDATE LOGIC FOR CART (Entire cart updates to 'Paid')
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $uid = $_SESSION['user_id'];
    $update = $pdo->prepare("UPDATE orders SET status = 'Paid' WHERE user_id = ? AND status = 'pending'");
    
    if ($update->execute([$uid]) && $update->rowCount() > 0) {
        $show_modal = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Caffinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary-gold: #d4a373; --glass-bg: rgba(0, 0, 0, 0.6); --text-light: #fefae0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
        body { 
            height: 100vh; 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('bgindex.JPG') no-repeat center center fixed; 
            background-size: cover; 
            display: flex; 
            flex-direction: column;
            color: var(--text-light); 
            overflow: hidden;
        }

        /* Top Navigation with Logo */
        .top-nav { 
            position: fixed; top: 0; width: 100%; padding: 15px 40px; 
            display: flex; justify-content: space-between; align-items: center; 
            background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(15px); 
            border-bottom: 1px solid rgba(212, 163, 115, 0.2); z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 15px; }
        .nav-left img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--primary-gold); }
        .nav-left span { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary-gold); }
        .nav-right a { color: #fff; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }

        /* Main Container */
        .customer-container { 
            margin: auto; width: 90%; max-width: 750px; 
            background: var(--glass-bg); backdrop-filter: blur(25px); 
            padding: 50px; border-radius: 30px; 
            border: 1px solid rgba(212, 163, 115, 0.15); text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        /* Title using the old font consistency */
        h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; color: var(--primary-gold); margin-bottom: 30px; }

        .customer-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .customer-actions a { 
            padding: 30px 15px; background: rgba(255, 255, 255, 0.05); 
            color: #fff; text-decoration: none; border-radius: 20px; 
            border: 1px solid rgba(255, 255, 255, 0.1); transition: 0.3s; 
            display: flex; flex-direction: column; align-items: center; gap: 10px; 
        }
        .customer-actions span { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }

        .customer-actions a:hover { 
            background: var(--primary-gold); color: #1a1a1a; 
            transform: translateY(-8px); border-color: var(--primary-gold);
        }
        
        /* Success Modal */
        #successModal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; justify-content: center; align-items: center; z-index: 9999; backdrop-filter: blur(10px); }
        .modal-content { background: #1a1a1a; padding: 40px; border-radius: 20px; border: 2px solid var(--primary-gold); text-align: center; }
        .btn-modal { margin-top: 20px; padding: 10px 30px; background: var(--primary-gold); border: none; border-radius: 50px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>

    <div id="successModal">
        <div class="modal-content">
            <h2 style="font-family:'Playfair Display'; color:var(--primary-gold);">Payment Success! ☕</h2>
            <p style="margin: 15px 0;">Your coffee is being prepared.</p>
            <button class="btn-modal" onclick="window.location.href='my_orders.php'">GO TO ORDERS</button>
        </div>
    </div>

    <nav class="top-nav">
        <div class="nav-left">
            <img src="indexpic.jpg" alt="Logo">
            <span>Cafinity</span>
        </div>
        <div class="nav-right"><a href="logout.php">Logout</a></div>
    </nav>

    <div class="customer-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
        <div class="customer-actions">
            <a href="order.php">
                <span style="font-size: 1.5rem;">🛒</span>
                <span>Order Now</span>
            </a>
            <a href="my_orders.php">
                <span style="font-size: 1.5rem;">📦</span>
                <span>My Orders</span>
            </a>
            <a href="profile.php">
                <span style="font-size: 1.5rem;">👤</span>
                <span>Profile</span>
            </a>
        </div>
    </div>

    <script>
        <?php if($show_modal): ?>
            document.getElementById('successModal').style.display = 'flex';
        <?php endif; ?>
    </script>
</body>
</html>