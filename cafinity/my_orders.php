<?php
session_start();
require "db.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }

$uid = $_SESSION['user_id'];

// Join orders with coffee table to get Names and Prices
$stmt = $pdo->prepare("SELECT o.*, c.name, c.price 
                       FROM orders o 
                       JOIN coffee c ON o.coffee_id = c.id 
                       WHERE o.user_id = ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$uid]);
$all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grouping logic: Items bought in the same second are treated as one "Cart"
$grouped_orders = [];
foreach ($all_orders as $order) {
    $time = $order['created_at'];
    $grouped_orders[$time][] = $order;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Caffinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4a373; --glass: rgba(0, 0, 0, 0.8); --border-gold: rgba(212, 163, 115, 0.3); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body { 
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('bgindex.JPG'); 
            background-size: cover; background-attachment: fixed; color: #fff; min-height: 100vh; padding: 80px 20px; 
        }

        .container { max-width: 600px; margin: 0 auto; background: var(--glass); padding: 40px; border-radius: 30px; border: 1px solid var(--border-gold); backdrop-filter: blur(15px); }
        h1 { font-family: 'Playfair Display', serif; color: var(--gold); text-align: center; margin-bottom: 30px; letter-spacing: 2px; }
        .back-link { color: var(--gold); text-decoration: none; font-size: 0.8rem; display: block; margin-bottom: 20px; text-transform: uppercase; }

        .order-group { 
            background: rgba(255, 255, 255, 0.03); padding: 25px; border-radius: 20px; 
            margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.08); transition: 0.3s;
        }
        .order-group:hover { border-color: var(--gold); background: rgba(255, 255, 255, 0.05); }

        .group-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid rgba(212,163,115,0.2); padding-bottom: 12px; margin-bottom: 15px; }
        .group-date { font-size: 0.75rem; color: #bbb; }
        
        .item-row { display: flex; justify-content: space-between; font-size: 0.95rem; margin-bottom: 8px; }
        .status-badge { font-size: 0.65rem; font-weight: 700; padding: 3px 12px; border-radius: 50px; text-transform: uppercase; display: inline-block; margin-top: 5px; }
        .status-paid { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .status-pending { background: rgba(212, 163, 115, 0.1); color: var(--gold); border: 1px solid var(--gold); }

        .btn-view {
            background: var(--gold); color: #000; padding: 10px; border-radius: 50px; 
            border: none; font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: 0.3s; width: 100%; margin-top: 20px;
        }
        .btn-view:hover { background: #fff; transform: translateY(-2px); }

        /* Thermal Receipt Modal */
        #receiptModal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: none; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(10px); }
        .receipt-box { background: #fff; color: #333; width: 90%; max-width: 350px; padding: 35px; border-radius: 5px; font-family: 'Courier New', Courier, monospace; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .receipt-header { text-align: center; border-bottom: 2px dashed #bbb; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-item { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 10px; }
        .total-row { border-top: 2px dashed #bbb; margin-top: 20px; padding-top: 15px; font-weight: bold; font-size: 1.1rem; display: flex; justify-content: space-between; }
        .close-receipt { margin-top: 25px; width: 100%; padding: 12px; background: #1a1a1a; color: #fff; border: none; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <div id="receiptModal">
        <div class="receipt-box">
            <div class="receipt-header">
                <h2 style="font-family:'Playfair Display', serif; margin-bottom: 5px;">CAFINITY</h2>
                <p style="font-size: 0.7rem; color: #666;">Official E-Receipt</p>
                <p id="r-date" style="font-size: 0.7rem; margin-top: 10px;"></p>
            </div>
            <div id="r-items-container"></div>
            <div class="total-row">
                <span>TOTAL PAID</span>
                <span id="r-total"></span>
            </div>
            <p style="text-align:center; font-size:0.8rem; margin-top:20px; color: #2ecc71; font-weight: bold;">Verified Transaction</p>
            <button class="close-receipt" onclick="closeReceipt()">DONE</button>
        </div>
    </div>

    <div class="container">
        <a href="dashboard.php" class="back-link">← Return to Dashboard</a>
        <h1>Order History</h1>

        <div class="order-list">
            <?php if(empty($grouped_orders)): ?>
                <p style="text-align:center; opacity:0.5; margin-top:50px;">No orders found yet.</p>
            <?php endif; ?>

            <?php foreach($grouped_orders as $timestamp => $items): 
                $total_price = 0;
                $status = $items[0]['status']; 
                $json_items = htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="order-group">
                <div class="group-header">
                    <div>
                        <p class="group-date"><?php echo date('F d, Y | h:i A', strtotime($timestamp)); ?></p>
                        <span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span>
                    </div>
                </div>

                <?php foreach($items as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_price += $subtotal;
                ?>
                    <div class="item-row">
                        <span><?php echo htmlspecialchars($item['name']); ?> <small>(x<?php echo $item['quantity']; ?>)</small></span>
                        <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div style="text-align:right; margin-top:15px; border-top:1px solid rgba(212,163,115,0.2); padding-top:10px;">
                    <span style="font-size: 0.8rem; opacity: 0.7;">Grand Total:</span><br>
                    <strong style="color:var(--gold); font-size: 1.2rem;">₱<?php echo number_format($total_price, 2); ?></strong>
                </div>

                <?php if(strtolower($status) == 'paid'): ?>
                    <button class="btn-view" onclick='openReceipt(<?php echo $json_items; ?>, "<?php echo date('M d, Y | h:i A', strtotime($timestamp)); ?>")'>
                        VIEW RECEIPT
                    </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function openReceipt(items, date) {
            const container = document.getElementById('r-items-container');
            container.innerHTML = '';
            let total = 0;

            items.forEach(item => {
                const sub = parseFloat(item.price) * parseInt(item.quantity);
                total += sub;
                container.innerHTML += `
                    <div class="receipt-item">
                        <span>${item.name} x${item.quantity}</span>
                        <span>₱${sub.toFixed(2)}</span>
                    </div>`;
            });

            document.getElementById('r-total').innerText = "₱" + total.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('r-date').innerText = date;
            document.getElementById('receiptModal').style.display = 'flex';
        }

        function closeReceipt() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        // Close modal when clicking background
        window.onclick = function(event) {
            if (event.target == document.getElementById('receiptModal')) {
                closeReceipt();
            }
        }
    </script>
</body>
</html>