<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Cafinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373; 
            --secondary-color: #1a1a1a; 
            --accent-color: #fefae0; 
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

        /* --- RESPONSIVE NAVIGATION --- */
        .top-nav {
            position: relative;
            width: 100%;
            padding: 40px 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            align-items: center;
            gap: 35px;
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

        .join-btn {
            border: 1px solid var(--primary-color);
            padding: 10px 25px;
            border-radius: 50px;
        }

        /* --- HERO SECTION --- */
        .hero-container {
            flex: 1; /* Pushes footer to bottom if content is short */
            display: flex;
            align-items: center;
            padding: 60px 80px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .content-area {
            max-width: 700px;
            animation: slideIn 1s ease-out;
        }

        .content-area h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 10vw, 5.5rem);
            line-height: 1.1;
            margin-bottom: 25px;
            color: var(--primary-color);
            text-transform: uppercase;
        }

        .content-area p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #ddd;
            margin-bottom: 45px;
            border-left: 3px solid var(--primary-color);
            padding-left: 20px;
        }

        /* --- BUTTONS --- */
        .btn {
            display: inline-block;
            padding: 20px 50px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.4s;
            background: var(--primary-color);
            color: #1a1a1a;
            border: 2px solid var(--primary-color);
        }

        .btn:hover {
            background: transparent;
            color: #fff;
            transform: translateY(-5px);
        }

        /* --- PROFESSIONAL FOOTER --- */
        footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 80px 80px 40px;
            border-top: 1px solid rgba(212, 163, 115, 0.1);
            margin-top: 50px;
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
            font-size: 1.2rem;
        }

        .footer-section p, .footer-section li {
            color: #888;
            font-size: 0.9rem;
            line-height: 1.8;
            list-style: none;
        }

        .footer-section a {
            color: #888;
            text-decoration: none;
            transition: 0.3s;
        }

        .footer-section a:hover { color: var(--primary-color); padding-left: 5px; }

        .footer-bottom {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            font-size: 0.8rem;
            color: #555;
            letter-spacing: 1px;
        }

        /* --- MOBILE MEDIA QUERIES --- */
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 992px) {
            .top-nav, footer { padding: 40px; }
            .hero-container { padding: 40px; }
        }

        @media (max-width: 768px) {
            .top-nav {
                flex-direction: column;
                padding: 20px;
                gap: 20px;
            }
            .nav-right {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            .nav-right a { font-size: 0.75rem; margin-left: 0; }
            
            .hero-container { padding: 40px 20px; text-align: center; }
            .content-area p { border-left: none; padding-left: 0; }
            footer { padding: 50px 20px 30px; }
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
            <a href="login.php">Login</a> <a href="register.php" class="join-btn">Join Us</a>
        </div>
    </nav>

    <main class="hero-container">
        <div class="content-area">
            <h1>TASTE AS GOOD<br>AS IT SMELLS</h1>
            <p>
                Experience the ultimate coffee journey with Cafinity.<br> 
                A moment of calm in every cup, crafted with passion.
            </p>
            <a href="order.php" class="btn">Order Now</a>
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
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="order.php">Menu</a></li>
                    <li><a href="contacts.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p>Email: hello@cafinity.com<br>
                   Phone: +1 (555) 123-4567<br>
                   Address: 123 Coffee Lane, Brew City</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 CAFINITY COFFEE CO. ALL RIGHTS RESERVED.
        </div>
    </footer>

</body>
</html>