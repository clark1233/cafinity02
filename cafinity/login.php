<?php
// Ensure NO spaces or text exist before this opening tag
session_start();
require "db.php";

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Sanitize and Grab Inputs
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (!empty($username) && !empty($password)) {
        try {
            // 2. Fetch User from Database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. Verify Password Hash
            if ($user && password_verify($password, $user["password"])) {
                
                // Regenerate Session ID for security
                session_regenerate_id(true);

                // Store User Data in Session
                $_SESSION["user_id"] = $user["id"];     
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                // 4. ROLE-BASED REDIRECT
                // We use exit() immediately after header to force the move
                if ($user["role"] === "admin") {
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    // IMPORTANT: Ensure "dashboard.php" exists in this same folder
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                // If the hash check fails or user isn't found
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Cafinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373; /* Golden Coffee */
            --secondary-color: #1a1a1a; /* Dark Roast */
            --glass-bg: rgba(0, 0, 0, 0.85);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

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

        .active-link { color: var(--primary-color) !important; }

        .join-btn {
            border: 1px solid var(--primary-color);
            padding: 10px 25px;
            border-radius: 50px;
            transition: 0.4s;
        }

        .join-btn:hover {
            background: var(--primary-color);
            color: var(--secondary-color);
        }

        /* --- LOGIN FORM --- */
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

        .error-msg {
            background: rgba(231, 76, 60, 0.2);
            color: #ff7675;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

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
            <a href="login.php" class="active-link">Login</a> 
            <a href="register.php" class="join-btn">Join Us</a>
        </div>
    </nav>

    <main class="login-container">
        <div class="login-card">
            <h2>Welcome Back</h2>
            <p class="subtitle">Enter your details to sign in</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <p class="register-link">
                Don't have an account? <a href="register.php">Create one here</a>
            </p>
        </div>
    </main>

    <footer>
        &copy; 2026 CAFINITY COFFEE CO. ALL RIGHTS RESERVED.
    </footer>

</body>
</html>