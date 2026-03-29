<?php
session_start();
require "db.php";

// 1. SECURITY CHECK
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit;
}

// 2. FETCH COFFEE MENU
$stmt = $pdo->query("SELECT * FROM coffee");
$coffees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. HANDLE ORDER SUBMISSION (PAYMONGO INTEGRATION)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buy_now'])) {
    $user_id = $_SESSION["user_id"];
    $coffee_id = $_POST["coffee_id"];
    $quantity = $_POST["quantity"];

    // Get coffee details
    $stmt = $pdo->prepare("SELECT * FROM coffee WHERE id = ?");
    $stmt->execute([$coffee_id]);
    $coffee = $stmt->fetch();

    if ($coffee) {
        $total_amount = ($coffee['price'] * $quantity) * 100; // PayMongo uses centavos

        // INSERT PENDING ORDER INTO DATABASE FIRST
        $insert = $pdo->prepare("INSERT INTO orders (user_id, coffee_id, quantity, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
        $insert->execute([$user_id, $coffee_id, $quantity]);

        // PAYMONGO API CALL
        $data = [
            'data' => [
                'attributes' => [
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount' => $total_amount,
                            'description' => $coffee['name'],
                            'name' => $coffee['name'],
                            'quantity' => (int)$quantity,
                        ]
                    ],
                    'payment_method_types' => ['gcash', 'paymaya', 'card'],
                    // CRITICAL: This URL tells dashboard.php to update the status to "Paid"
                    'success_url' => 'http://localhost/cafinity/dashboard.php?status=success',
                    'cancel_url' => 'http://localhost/cafinity/place_order.php',
                    'description' => 'Caffinity Coffee Order'
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/checkout_sessions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // Replace with your actual Public/Secret Key
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode('sk_test_YOUR_SECRET_KEY_HERE') 
        ]);

        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        if (isset($result['data']['attributes']['checkout_url'])) {
            header("Location: " . $result['data']['attributes']['checkout_url']);
            exit;
        } else {
            echo "<script>alert('Payment Gateway Error. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order | Caffinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #d4a373;
            --glass: rgba(0, 0, 0, 0.75);
            --text: #fefae0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), 
                        url('bgindex.JPG') no-repeat center center fixed;
            background-size: cover;
            color: var(--text);
            padding: 100px 20px;
        }

        /* --- Navigation --- */
        .top-nav {
            position: fixed; top: 0; left: 0; width: 100%; padding: 15px 40px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(15px);
            z-index: 1000; border-bottom: 1px solid rgba(212, 163, 115, 0.2);
        }
        .nav-left span { font-family: 'Playfair Display'; color: var(--primary-gold); font-size: 1.5rem; }
        .nav-right a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 0.8rem; text-transform: uppercase; }

        /* --- Menu Grid --- */
        .menu-container {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px; padding: 20px;
        }

        .coffee-card {
            background: var(--glass); backdrop-filter: blur(15px);
            border-radius: 25px; border: 1px solid rgba(255,255,255,0.1);
            padding: 30px; text-align: center; transition: 0.4s;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .coffee-card:hover { transform: translateY(-10px); border-color: var(--primary-gold); }

        .coffee-card h3 { font-family: 'Playfair Display'; color: var(--primary-gold); font-size: 1.8rem; margin-bottom: 10px; }
        .coffee-card p { font-size: 0.9rem; opacity: 0.8; margin-bottom: 20px; }
        .price { font-size: 1.4rem; font-weight: 600; color: #fff; margin-bottom: 20px; }

        .order-form { display: flex; flex-direction: column; gap: 15px; }
        .qty-input {
            background: rgba(255,255,255,0.05); border: 1px solid #555;
            color: #fff; padding: 10px; border-radius: 10px; text-align: center;
        }

        .btn-buy {
            background: var(--primary-gold); color: #1a1a1a;
            padding: 15px; border-radius: 50px; border: none;
            font-weight: 700; cursor: pointer; text-transform: uppercase;
            transition: 0.3s;
        }
        .btn-buy:hover { letter-spacing: 1px; box-shadow: 0 5px 15px rgba(212, 163, 115, 0.4); }

        h1 { font-family: 'Playfair Display'; text-align: center; font-size: 3rem; color: var(--primary-gold); margin-bottom: 50px; }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div class="nav-left"><span>Caffinity</span></div>
        <div class="nav-right">
            <a href="dashboard.php">Dashboard</a>
            <a href="my_orders.php">My Orders</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <h1>Select Your Brew</h1>

    <div class="menu-container">
        <?php foreach ($coffees as $item): ?>
            <div class="coffee-card">
                <div>
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <div class="price">₱<?php echo number_format($item['price'], 2); ?></div>
                </div>

                <form class="order-form" method="POST">
                    <input type="hidden" name="coffee_id" value="<?php echo $item['id']; ?>">
                    <label style="font-size: 0.8rem; opacity: 0.7;">Quantity</label>
                    <input type="number" name="quantity" class="qty-input" value="1" min="1" max="10">
                    <button type="submit" name="buy_now" class="btn-buy">Buy Now with GCash</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>