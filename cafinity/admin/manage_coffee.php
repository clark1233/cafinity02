<?php
session_start();
require "../db.php";

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Delete coffee
if (isset($_POST['confirm_delete_coffee'])) {
    $coffee_id = $_POST['coffee_id'];
    $stmt = $pdo->prepare("DELETE FROM coffee WHERE id=?");
    $stmt->execute([$coffee_id]);
    header("Location: manage_coffee.php?status=deleted");
    exit;
}

// Add coffee
if (isset($_POST['add_coffee'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $image = $_FILES['image']['name'];
    $target = "../uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO coffee (name, price, image, rating) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $image, $rating]);
        header("Location: manage_coffee.php?status=added");
        exit;
    }
}

// Update coffee
if (isset($_POST['update_coffee'])) {
    $coffee_id = $_POST['coffee_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $stmt = $pdo->prepare("UPDATE coffee SET name=?, price=?, rating=?, image=? WHERE id=?");
        $stmt->execute([$name, $price, $rating, $image, $coffee_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE coffee SET name=?, price=?, rating=? WHERE id=?");
        $stmt->execute([$name, $price, $rating, $coffee_id]);
    }

    header("Location: manage_coffee.php?status=updated");
    exit;
}

$stmt = $pdo->query("SELECT * FROM coffee ORDER BY id ASC");
$coffees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Menu | Caffinity Admin</title>

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

/* FORM + TABLE stacked like Users page */
.form-add {
    margin-bottom:30px;
}

.form-add h3 {
    color:var(--primary-gold);
    font-family:'Playfair Display', serif;
    margin-bottom:20px;
}

.form-add form {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}

.form-add input[type=text], .form-add input[type=number], .form-add select, .form-add input[type=file] {
    padding:10px;
    border-radius:10px;
    border:1px solid var(--border-glass);
    background:rgba(0,0,0,0.3);
    color:#fff;
}

.form-add input[type=text], .form-add input[type=file] { flex:1; }
.form-add input[type=number], .form-add select { width:150px; }

.btn-gold {
    padding:12px 20px;
    background:var(--primary-gold);
    color:#1a120b;
    border:none;
    border-radius:50px;
    cursor:pointer;
    font-weight:600;
}

.btn-gold:hover {
    background:#fff;
    transform:translateY(-2px);
}

/* TABLE (now grid) */
.table-container { padding:30px; width:100%; background:transparent; }

.coffee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.coffee-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid rgba(255,255,255,0.2);
}

.coffee-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.35);
}

.coffee-card .coffee-img {
    width:120px;
    height:120px;
    margin-bottom:15px;
    border-radius:15px;
    object-fit:cover;
    border:2px solid var(--primary-gold);
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

.coffee-card form { width:100%; display:flex; flex-direction:column; align-items:center; gap:8px; }

.coffee-card input[type=text],
.coffee-card input[type=number],
.coffee-card select {
    background: rgba(255,255,255,0.2);
    border: none;
    padding: 8px 10px;
    border-radius: 10px;
    color: #1a120b;
    font-weight: 500;
}

.custom-file-container {
    width: 100%;
    text-align: center;
}

.custom-file-input {
    display: none;
}

.custom-file-label {
    display: inline-block;
    padding: 8px 15px;
    background: var(--primary-gold);
    color: #1a120b;
    border-radius: 50px;
    cursor: pointer;
    font-weight:600;
    transition: background 0.3s;
}
.custom-file-label:hover {
    background: #fff;
}

.file-name {
    display: block;
    margin-top: 5px;
    font-size: 0.85rem;
    color: #ccc;
}

.coffee-card input[type=text],
.coffee-card input[type=number],
.coffee-card select { width:100%; }


/* add button container */
.add-button-container { display:flex; justify-content:flex-end; }

/* modal adjustments (reuse existing modal classes) */
.modal-content form input[type=text],
.modal-content form input[type=number],
.modal-content form select,
.modal-content form input[type=file] {
    width:100%;
    margin:5px 0;
}

.btn-delete, .btn-save-sm {
    padding:8px 18px;
    border-radius:50px;
    cursor:pointer;
    transition:0.3s;
    font-weight:600;
}

.btn-delete {
    background:transparent;
    color:var(--danger);
    border:1px solid var(--danger);
}

.btn-delete:hover { background:var(--danger); color:#fff; }

.btn-save-sm {
    background:var(--primary-gold);
    color:#1a120b;
    border:none;
    margin-right:5px;
}

.btn-save-sm:hover {
    background:#fff;
}

/* MODAL */
.modal-overlay {
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.85);
    backdrop-filter:blur(8px);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:2000;
}

.modal-content {
    background:#1a120b;
    padding:40px;
    border-radius:30px;
    border:1px solid var(--primary-gold);
    text-align:center;
    max-width:400px;
    width:90%;
}

::-webkit-scrollbar { width:6px; }
::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.2); border-radius:10px; }
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
            <li class="nav-item"><a href="manage_coffee.php" class="nav-link active"><i class="fa-solid fa-mug-hot"></i> Menu</a></li>
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
                <input type="text" placeholder="Search menu items...">
            </div>

    <div class="glass-panel" style="padding:8px 20px; display:flex; align-items:center; gap:12px;">
        <span style="font-weight:600;">Admin Console</span>
        <i class="fa-solid fa-user-shield" style="color:var(--primary-gold);"></i>
    </div>
</header>

<main class="content">
<h1 class="page-title">Manage Coffee Menu</h1>

<div class="add-button-container" style="padding:0 40px 20px;">
    <button class="btn-gold" onclick="showAddModal()">Add New Item</button>
</div>

<div class="table-container">
    <div class="coffee-grid">
    <?php foreach($coffees as $coffee): ?>
        <div class="coffee-card">
            <img src="../uploads/<?php echo $coffee['image']; ?>" class="coffee-img">
            <form method="post" enctype="multipart/form-data" id="update-<?php echo $coffee['id']; ?>">
                <input type="hidden" name="coffee_id" value="<?php echo $coffee['id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($coffee['name']); ?>" placeholder="Name" required>
                <input type="number" name="price" value="<?php echo $coffee['price']; ?>" step="0.01" placeholder="Price" required>
                <select name="rating" required>
                    <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php if($coffee['rating']==$i) echo "selected"; ?>><?php echo $i; ?>★</option>
                    <?php endfor; ?>
                </select>
                <div class="custom-file-container">
    <input type="file" name="image" accept="image/*" id="file-<?php echo $coffee['id']; ?>" class="custom-file-input">
    <label for="file-<?php echo $coffee['id']; ?>" class="custom-file-label">Choose Image</label>
    <span class="file-name" id="file-name-<?php echo $coffee['id']; ?>"></span>
</div>

<script>
    document.getElementById('file-<?php echo $coffee['id']; ?>').addEventListener('change', function(e){
        var fileName = e.target.files[0] ? e.target.files[0].name : '';
        document.getElementById('file-name-<?php echo $coffee['id']; ?>').innerText = fileName;
    });
</script>
                <div style="margin-top:10px; display:flex; gap:5px; justify-content:center;">
                    <button type="submit" name="update_coffee" class="btn-save-sm">Save</button>
                    <button type="button" class="btn-delete" onclick="showDeleteModal(<?php echo $coffee['id']; ?>, '<?php echo htmlspecialchars($coffee['name']); ?>')">Delete</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
    </div>
</div>

</main>
</div>

<div id="deleteModal" class="modal-overlay">
<div class="modal-content">
<h2 id="modalTitle" style="font-family:'Playfair Display'; color:var(--primary-gold);">Delete Coffee?</h2>
<p style="color:#ccc; margin:15px 0 25px;">This will permanently remove this item from your menu.</p>
<form method="post">
<input type="hidden" name="coffee_id" id="deleteCoffeeId">
<div style="display:flex; gap:10px;">
<button type="button" class="btn-gold" style="background:#333; color:#fff;" onclick="closeDeleteModal()">Cancel</button>
<button type="submit" name="confirm_delete_coffee" class="btn-gold" style="background:var(--danger); color:#fff;">Delete</button>
</div>
</form>
</div>
</div>

<!-- Add/Edit modal for creating new coffee -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content">
        <h3 class="modal-title" style="font-family:'Playfair Display'; color:var(--primary-gold);">Add New Coffee</h3>
        <form method="post" enctype="multipart/form-data" id="addForm">
            <input type="text" name="name" placeholder="Coffee Name" required>
            <input type="number" name="price" placeholder="Price (₱)" step="0.01" required>
            <select name="rating" required>
                <option value="">Rating</option>
                <?php for($i=1;$i<=5;$i++) echo "<option value='$i'>$i★</option>"; ?>
            </select>
            <div class="custom-file-container">
            <input type="file" name="image" accept="image/*" id="add-file" class="custom-file-input" required>
            <label for="add-file" class="custom-file-label">Choose Image</label>
            <span class="file-name" id="add-file-name"></span>
        </div>
        <script>
            document.getElementById('add-file').addEventListener('change', function(e){
                var fileName = e.target.files[0] ? e.target.files[0].name : '';
                document.getElementById('add-file-name').innerText = fileName;
            });
        </script>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button type="button" class="btn-gold" style="background:#333; color:#fff;" onclick="closeAddModal()">Cancel</button>
                <button type="submit" name="add_coffee" class="btn-gold">Add</button>
            </div>
        </form>
    </div>
</div>

<script>
function showDeleteModal(id, name) {
    document.getElementById('deleteCoffeeId').value = id;
    document.getElementById('modalTitle').innerText = "Delete " + name + "?";
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function showAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('addForm').reset();
}
</script>

</body>
</html>