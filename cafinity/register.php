<?php
session_start();
require "db.php";

$error = ""; 
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars(trim($_POST["username"]));
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    $uppercase = preg_match('@[A-Z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (strlen($username) < 4) {
        $error = "Username must be at least 4 characters.";
    } elseif (!$uppercase || !$number || !$specialChars || strlen($password) < 8) {
        $error = "Password must be 8+ characters (Uppercase, Number, Symbol).";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username is already taken.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')");
                if ($stmt->execute([$username, $hashed_password])) {
                    $success = "Account created! You can now sign in.";
                } else {
                    $error = "System error. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Cafinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373; 
            --secondary-color: #1a1a1a; 
            --glass-bg: rgba(0, 0, 0, 0.85);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('bgindex.JPG') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- NAVIGATION --- */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px 80px;
            width: 100%;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            letter-spacing: 1px;
        }

        .nav-left img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
        }

        .nav-right {
            display: flex;
            gap: 35px;
            align-items: center;
        }

        .nav-right a {
            color: #ffffff;
            font-size: 0.85rem;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.3s;
        }

        .nav-right a:hover { color: var(--primary-color); }

        /* Modified Login Button to look like the "Join Us" pill */
        .login-nav-pill {
            border: 1px solid var(--primary-color);
            padding: 10px 25px;
            border-radius: 50px;
            transition: 0.4s;
            color: var(--primary-color) !important;
        }

        .login-nav-pill:hover {
            background: var(--primary-color);
            color: var(--secondary-color) !important;
        }

        /* --- FORM CARD --- */
        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 50px 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            border: 1px solid rgba(212, 163, 115, 0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .login-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .subtitle { color: #bbb; margin-bottom: 35px; font-size: 0.95rem; }

        input {
            width: 100%;
            padding: 18px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
        }

        input:focus { outline: none; border-color: var(--primary-color); }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: var(--primary-color);
            color: var(--secondary-color);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.4s;
            margin-top: 10px;
        }

        .login-btn:hover { background: #fefae0; transform: translateY(-2px); }

        .msg-box { padding: 12px; border-radius: 10px; margin-bottom: 25px; font-size: 0.85rem; border: 1px solid; }
        .error-msg { background: rgba(231, 76, 60, 0.2); color: #ff7675; border-color: rgba(231, 76, 60, 0.3); }
        .success-msg { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border-color: rgba(46, 204, 113, 0.3); }

        .register-link { margin-top: 30px; color: #bbb; font-size: 0.9rem; }
        .register-link a { color: var(--primary-color); text-decoration: none; font-weight: 600; }

        footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 40px;
            text-align: center;
            border-top: 1px solid rgba(212, 163, 115, 0.1);
            color: #555;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .top-nav { padding: 20px; flex-direction: column; gap: 20px; }
            .nav-right { gap: 15px; }
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div class="nav-left">
            <img src="indexpic.jpg" alt="Logo">
            <span>CAFINITY</span>
        </div>
        <div class="nav-right">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contacts.php">Contacts</a>
            <a href="login.php" class="login-nav-pill">Login</a> 
        </div>
    </nav>

    <main class="login-container">
        <div class="login-card">
            <h2>Join Us</h2>
            <p class="subtitle">Create an account to get started</p>

            <?php if (!empty($error)): ?>
                <div class="msg-box error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="msg-box success-msg"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" class="login-btn">Sign Up</button>
            </form>

            <p class="register-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </p>
        </div>
    </main>

    <footer>
        &copy; 2026 CAFINITY COFFEE CO. ALL RIGHTS RESERVED.
    </footer>

</body>
</html>