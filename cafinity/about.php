<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Cafinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373; 
            --secondary-color: #1a1a1a; 
            --glass-bg: rgba(0, 0, 0, 0.75);
            --cream: #fefae0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
        body {
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), 
                        url('bgindex.JPG') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- NAVIGATION (EXACT MATCH TO INDEX) --- */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px 80px; 
            width: 100%;
            z-index: 1000;
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

        /* The 'Join Us' style preserved from Index */
        .join-btn {
            border: 1px solid var(--primary-color);
            padding: 10px 25px;
            border-radius: 50px;
            transition: 0.4s;
        }

        .join-btn:hover {
            background: var(--primary-color);
            color: var(--secondary-color) !important;
        }

        /* --- ABOUT CONTENT --- */
        .about-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            max-width: 1400px;
            margin: 0 auto;
            gap: 80px;
        }

        .about-text { flex: 1; }

        .about-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 25px;
            line-height: 1.1;
        }

        .about-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #ccc;
            margin-bottom: 20px;
        }

        .highlight-box {
            background: rgba(212, 163, 115, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            margin-top: 30px;
            font-style: italic;
            color: var(--cream);
        }

        .about-image-stack {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(212, 163, 115, 0.2);
            box-shadow: 0 30px 60px rgba(0,0,0,0.6);
            text-align: center;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item h2 {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-family: 'Playfair Display', serif;
        }

        .stat-item p {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
        }

        /* --- FOOTER (EXACT MATCH TO INDEX) --- */
        footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 80px 80px 40px;
            border-top: 1px solid rgba(212, 163, 115, 0.1);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-section h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 25px;
        }

        .footer-section p, .footer-section li { color: #888; font-size: 0.9rem; line-height: 1.8; list-style: none; }

        .footer-bottom {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            font-size: 0.8rem;
            color: #555;
        }

        @media (max-width: 992px) {
            .about-container { flex-direction: column; padding: 40px; text-align: center; }
            .top-nav { padding: 30px 40px; }
            .about-text h1 { font-size: 3rem; }
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
            <a href="about.php" style="color: var(--primary-color);">About</a>
            <a href="contacts.php">Contacts</a>
            <a href="login.php">Login</a> <a href="register.php" class="join-btn">Join Us</a> </div>
    </nav>

    <main class="about-container">
        <div class="about-text">
            <h1>Our Story</h1>
            <p>Founded in 2024, Cafinity began with a simple mission: to bridge the gap between premium coffee culture and effortless digital management. We believe that every cup tells a story, and every shop deserves a system as smooth as its finest roast.</p>
            <p>Our team blends the art of brewing with the precision of modern technology to provide an unparalleled experience for both coffee enthusiasts and business owners alike.</p>
            
            <div class="highlight-box">
                "We don't just manage coffee shops; we nurture the passion that goes into every single bean."
            </div>
        </div>

        <div class="about-image-stack">
            <div class="glass-card">
                <img src="indexpic.jpg" alt="Cafinity" style="width: 80px; margin-bottom: 20px; border-radius: 50%; border: 2px solid var(--primary-color);">
                <h3 style="font-family: 'Playfair Display'; margin-bottom: 10px;">Why Cafinity?</h3>
                <p style="font-size: 0.9rem; margin-bottom: 20px;">Dedicated to excellence in every transaction.</p>
                
                <div class="stat-grid">
                    <div class="stat-item">
                        <h2>50+</h2>
                        <p>Partner Shops</p>
                    </div>
                    <div class="stat-item">
                        <h2>10k+</h2>
                        <p>Users</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-grid">
            <div class="footer-section">
                <h3>CAFINITY</h3>
                <p>Crafting the perfect brew since 2024. A moment of calm in every cup, crafted with passion and excellence.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul style="padding:0;">
                    <li><a href="index.php" style="color:#888; text-decoration:none;">Home</a></li>
                    <li><a href="about.php" style="color:#888; text-decoration:none;">About Us</a></li>
                    <li><a href="contacts.php" style="color:#888; text-decoration:none;">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>hello@cafinity.com<br>123 Coffee Lane, Brew City</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 CAFINITY COFFEE CO. ALL RIGHTS RESERVED.
        </div>
    </footer>

</body>
</html>