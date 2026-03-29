<?php
session_start();
require "db.php"; // <--- Ensure this file connects to your database

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$descriptions = [
    "Espresso" => "A concentrated shot of coffee brewed under high pressure.",
    "Cappuccino" => "Equal parts espresso, steamed milk, and foamed milk.",
    "Latte" => "Espresso mixed with a generous amount of steamed milk.",
    "Americano" => "A shot of espresso diluted with hot water.",
    "Cold Brew" => "Coffee grounds steeped in cold water.",
    "Matcha Latte" => "Premium green tea matcha whisked with steamed milk."
];

// Handle AJAX Cart Actions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $id = $_POST['coffee_id'];
        $qty = (int)$_POST['quantity'];
        $name = $_POST['name'];
        $price = $_POST['price'];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$id] = ['name' => $name, 'price' => $price, 'quantity' => $qty];
        }
        echo count($_SESSION['cart']); 
        exit;
    }
    // THE FIX: DELETE INDIVIDUAL ITEM LOGIC
    if ($_POST['action'] == 'delete') {
        $id = $_POST['coffee_id'];
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        echo count($_SESSION['cart']);
        exit;
    }
    if ($_POST['action'] == 'clear') {
        $_SESSION['cart'] = [];
        header("Location: order.php");
        exit;
    }
}

// PayMongo Checkout Logic
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    $line_items = [];
    foreach ($_SESSION['cart'] as $id => $item) {
        $line_items[] = [
            'currency' => 'PHP',
            'amount' => ($item['price'] * $item['quantity']) * 100, // PayMongo uses centavos
            'description' => 'Caffinity Order',
            'name' => $item['name'],
            'quantity' => $item['quantity']
        ];
        // Insert into DB as 'pending'
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, coffee_id, quantity, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->execute([$_SESSION["user_id"], $id, $item['quantity']]);
    }

    $payload = json_encode(['data' => ['attributes' => [
        'line_items' => $line_items,
        'payment_method_types' => ['gcash', 'card', 'paymaya'],
        'success_url' => 'http://localhost/cafinity/dashboard.php?status=success', // <--- Set your actual domain here
        'description' => "Order for " . $_SESSION['username']
    ]]]);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode("sk_test_AhL2222giv2j4aDJdzFaKdym:")
        ],
    ]);

    $response = curl_exec($curl);
    $session_data = json_decode($response, true);
    curl_close($curl);

    if (isset($session_data['data']['attributes']['checkout_url'])) {
        $_SESSION['cart'] = []; // Clear local cart on success
        header("Location: " . $session_data['data']['attributes']['checkout_url']);
        exit;
    }
}

// Fetch only one unique coffee type per name
$unique_coffees = $pdo->query("SELECT MIN(id) as id, name, price, rating FROM coffee GROUP BY name ORDER BY MIN(id) ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order | Caffinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4a373; --glass: rgba(0, 0, 0, 0.85); --glass-light: rgba(255, 255, 255, 0.05); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
        body { 
            min-height: 100vh; background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('bgindex.JPG') no-repeat center center fixed; 
            background-size: cover; color: #fff; padding: 120px 20px 50px; overflow-x: hidden; 
        }
        
        /* Navigation */
        .top-nav { position: fixed; top: 0; left: 0; width: 100%; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(15px); z-index: 1000; border-bottom: 1px solid rgba(212, 163, 115, 0.2); }
        .nav-left { display: flex; align-items: center; gap: 12px; }
        .nav-left img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--gold); }
        .nav-left span { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--gold); }
        .nav-right a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; }

        /* Cart Elements */
        .cart-toggle { position: fixed; right: 30px; bottom: 30px; background: var(--gold); color: #000; width: 60px; height: 60px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; z-index: 1001; box-shadow: 0 10px 20px rgba(0,0,0,0.5); font-size: 1.5rem; transition: 0.3s; }
        #cart-count { position: absolute; top: 0; right: 0; background: #ff4d4d; color: #fff; font-size: 0.7rem; width: 22px; height: 22px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }

        .cart-panel { position: fixed; right: -360px; top: 0; width: 340px; height: 100vh; background: var(--glass); backdrop-filter: blur(25px); border-left: 1px solid var(--gold); padding: 100px 30px 30px; z-index: 1000; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; }
        .cart-panel.active { right: 0; }
        .cart-panel h2 { font-family: 'Playfair Display'; color: var(--gold); margin-bottom: 20px; border-bottom: 1px solid rgba(212,163,115,0.2); padding-bottom: 10px; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px; }
        .delete-item { color: #ff7675; cursor: pointer; font-size: 1.2rem; background: none; border: none; padding: 0 5px; margin-left: 10px; font-weight: bold; }

        /* Coffee Grid */
        h1 { font-family: 'Playfair Display', serif; text-align: center; font-size: 3rem; color: var(--gold); margin-bottom: 50px; }
        .container { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; transition: 0.4s; }
        
        .card { background: rgba(0,0,0,0.6); border-radius: 20px; overflow: hidden; border: 1px solid var(--glass-light); text-align: center; transition: 0.3s; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-10px); border-color: var(--gold); }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        .card-content { padding: 20px; flex-grow: 1; }
        .card h3 { font-family: 'Playfair Display'; color: var(--gold); font-size: 1.5rem; margin-bottom: 10px; }
        .rating { color: var(--gold); margin-bottom: 15px; }

        .btn-add { width: 100%; padding: 12px; background: var(--gold); color: #000; border: none; cursor: pointer; font-weight: 600; border-radius: 50px; text-transform: uppercase; margin-top: 10px; }
        .btn-checkout { width: 100%; padding: 15px; background: #2ecc71; color: #fff; border: none; cursor: pointer; border-radius: 50px; font-weight: 700; margin-top: auto; }
        select { width: 100%; padding: 8px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(212,163,115,0.3); border-radius: 5px; margin-bottom: 10px; }

        @media (max-width: 768px) { h1 { font-size: 2.2rem; } .nav-right a { font-size: 0.7rem; } }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-left">
        <img src="indexpic.jpg" alt="Logo">
        <span>CAFINITY</span>
    </div>
    <div class="nav-right">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_orders.php">My Orders</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="cart-toggle" onclick="toggleCart()">
    🛒 <div id="cart-count"><?php echo count($_SESSION['cart']); ?></div>
</div>

<div class="cart-panel" id="cart-panel">
    <h2>Your Basket</h2>
    <div id="cart-items-list" style="flex-grow: 1; overflow-y: auto;">
        <?php foreach($_SESSION['cart'] as $id => $item): ?>
            <div class="cart-item">
                <div style="flex-grow: 1;">
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></span><br>
                    x<?php echo $item['quantity']; ?> | ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>
                <button class="delete-item" onclick="deleteFromCart('<?php echo $id; ?>')">×</button>
            </div>
        <?php endforeach; ?>
    </div>
    
    <form method="post">
        <?php if(!empty($_SESSION['cart'])): ?>
            <button type="submit" name="checkout" class="btn-checkout">CHECKOUT NOW</button>
            <button type="submit" name="action" value="clear" style="background:none; border:none; color:#ff7675; font-size:0.7rem; cursor:pointer; width:100%; margin-top:15px; text-transform:uppercase; letter-spacing:1px;">Clear Entire Basket</button>
        <?php else: ?>
            <p style="text-align: center; opacity: 0.5; font-size: 0.8rem; padding: 20px;">Your basket is empty...</p>
        <?php endif; ?>
    </form>
</div>

<h1>Our Special Blends</h1>
<div class="container">
    <?php foreach ($unique_coffees as $index => $coffee): 
        // Image logic: loop coffee1.jpg to coffee13.jpg
        $img_num = ($index % 13) + 1; $img_name = "coffee" . $img_num . ".jpg";
    ?>
    <div class="card">
        <img src="uploads/<?php echo $img_name; ?>" onerror="this.src='uploads/default.jpg'" alt="<?php echo htmlspecialchars($coffee['name']); ?>">
        <div class="card-content">
            <h3><?php echo htmlspecialchars($coffee['name']); ?></h3>
            <p style="font-size:0.8rem; color:#bbb; margin-bottom:15px; height: 36px; overflow: hidden;"><?php echo $descriptions[$coffee['name']] ?? "Fresh brew."; ?></p>
            <div class="rating">
                <?php for($i=0; $i<$coffee['rating']; $i++) echo "★"; ?>
                <?php for($i=$coffee['rating']; $i<5; $i++) echo "☆"; ?>
            </div>
            <p style="color:var(--gold); font-weight:600; font-size:1.4rem; margin-bottom:10px;">₱<?php echo number_format($coffee['price'], 2); ?></p>
            <form onsubmit="addToCart(event, this)">
                <input type="hidden" name="coffee_id" value="<?php echo $coffee['id']; ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($coffee['name']); ?>">
                <input type="hidden" name="price" value="<?php echo $coffee['price']; ?>">
                <select name="quantity">
                    <?php for($i=1; $i<=5; $i++) echo "<option value='$i'>$i Cup(s)</option>"; ?>
                </select>
                <button type="submit" class="btn-add">Add to Basket</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleCart() {
    document.getElementById('cart-panel').classList.toggle('active');
}

function addToCart(e, form) {
    e.preventDefault();
    const formData = new FormData(form);
    formData.append('action', 'add');
    fetch('order.php', { method: 'POST', body: formData })
    .then(res => res.text()).then(() => {
        location.reload(); // Refresh to update list items
    });
}

function deleteFromCart(coffeeId) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('coffee_id', coffeeId);
    fetch('order.php', { method: 'POST', body: formData })
    .then(res => res.text()).then(() => {
        location.reload(); // Refresh to update list items
    });
}
</script>
</body>
</html>