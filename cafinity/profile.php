<?php
session_start();
require "db.php";

/* 🔐 Protect page - Only customers can access */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit;
}

/* 📥 Fetch user data */
$stmt = $pdo->prepare("SELECT username, role, profile_image, password FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = "";
$success = "";

/* 📸 Profile picture upload logic */
if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] === 0) {
    $ext = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png"];

    if (in_array($ext, $allowed)) {
        $fileName = "user_" . $_SESSION["user_id"] . "_" . time() . "." . $ext; // Added time() to prevent cache issues
        if(!is_dir('uploads')) mkdir('uploads', 0777, true); 
        
        if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "uploads/$fileName")) {
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$fileName, $_SESSION["user_id"]]);
            header("Location: profile.php?upload=success");
            exit;
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        $error = "Only JPG, JPEG, and PNG files are allowed.";
    }
}

/* 🔑 Change password logic */
if (isset($_POST["current_password"])) {
    if (!password_verify($_POST["current_password"], $user["password"])) {
        $error = "Current password is incorrect.";
    } elseif ($_POST["new_password"] !== $_POST["confirm_password"]) {
        $error = "New passwords do not match.";
    } else {
        $newHash = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $_SESSION["user_id"]]);
        $success = "Password updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Caffinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4a373;
            --dark: #1a1a1a;
            --glass: rgba(255, 255, 255, 0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('bgindex.JPG') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* --- Navigation --- */
        .top-nav {
            position: fixed; top: 0; left: 0; width: 100%; padding: 15px 40px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(15px); z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .nav-left { display: flex; align-items: center; gap: 12px; }
        .nav-left img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--gold); }
        .nav-right a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .nav-right a:hover { color: var(--gold); }

        /* --- Profile Container --- */
        .container {
            margin: 120px auto 50px;
            width: 90%;
            max-width: 500px;
            background: var(--glass);
            backdrop-filter: blur(25px);
            padding: 40px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            text-align: center;
        }

        h1 { font-family: 'Playfair Display', serif; color: var(--gold); margin-bottom: 30px; font-size: 2.2rem; }

        /* --- Profile Image & Button Styling --- */
        .profile-pic { 
            position: relative; 
            margin-bottom: 35px; 
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-pic img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            object-fit: cover;
            background: #222;
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
        
        .btn-upload {
            display: inline-block;
            padding: 10px 24px;
            background: rgba(212, 163, 115, 0.15);
            border: 1px solid var(--gold);
            border-radius: 50px;
            color: var(--gold);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-upload:hover {
            background: var(--gold);
            color: var(--dark);
            transform: translateY(-2px);
        }
        input[type=file] { display: none; }

        .user-info { margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .user-info p { margin-bottom: 8px; font-size: 1rem; color: #ddd; }
        .user-info span { color: var(--gold); font-weight: 600; }

        /* --- Form Elements --- */
        h3 { font-family: 'Playfair Display', serif; margin: 30px 0 20px; color: var(--gold); font-size: 1.5rem; }
        form { text-align: left; }
        label { display: block; margin-top: 15px; font-size: 0.75rem; color: #aaa; text-transform: uppercase; letter-spacing: 1px; }
        input[type="password"] {
            width: 100%; padding: 14px; margin-top: 5px; background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff;
            outline: none; transition: 0.3s;
        }
        input[type="password"]:focus { border-color: var(--gold); background: rgba(0,0,0,0.6); }

        .btn-submit {
            width: 100%; margin-top: 30px; padding: 16px; background: var(--gold);
            border: none; border-radius: 50px; color: var(--dark);
            font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-submit:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        .message { padding: 12px; border-radius: 10px; margin-top: 20px; font-size: 0.85rem; text-align: center; }
        .error { background: rgba(231, 76, 60, 0.2); color: #ff7675; border: 1px solid #e74c3c; }
        .success { background: rgba(39, 174, 96, 0.2); color: #55efc4; border: 1px solid #27ae60; }
    </style>
</head>

<body>

<nav class="top-nav">
    <div class="nav-left">
        <img src="indexpic.jpg" alt="Logo">
        <span style="font-family: 'Playfair Display'; font-size: 1.4rem; margin-left:10px; letter-spacing:1px;">Caffinity</span>
    </div>
    <div class="nav-right">
        <a href="dashboard.php">Dashboard</a>
        <a href="menu.php">Menu</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>My Account</h1>

    <div class="profile-pic">
        <?php 
            $pic = !empty($user["profile_image"]) ? "uploads/" . $user["profile_image"] : "https://via.placeholder.com/140/333/d4a373?text=User";
        ?>
        <img src="<?php echo $pic; ?>" alt="User Profile">
        
        <form method="post" enctype="multipart/form-data" id="picForm">
            <label for="file-upload" class="btn-upload">
                Change Photo
            </label>
            <input id="file-upload" type="file" name="profile_pic" onchange="document.getElementById('picForm').submit()">
        </form>
    </div>

    <div class="user-info">
        <p>Username: <span><?php echo htmlspecialchars($user["username"]); ?></span></p>
        <p>Account Type: <span><?php echo ucfirst(htmlspecialchars($user["role"])); ?></span></p>
    </div>

    <h3>Security Settings</h3>

    <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
    <?php if (isset($_GET['upload']) && $_GET['upload'] == 'success'): ?>
        <div class="message success">Profile photo updated!</div>
    <?php endif; ?>

    <form method="post">
        <label>Current Password</label>
        <input type="password" name="current_password" required placeholder="••••••••">

        <label>New Password</label>
        <input type="password" name="new_password" required placeholder="Enter new password">

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required placeholder="Repeat new password">

        <button type="submit" class="btn-submit">Update Password</button>
    </form>
</div>

</body>
</html>