<?php
session_start();
$msg_sent = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Logic for handling the form submission would go here
    $msg_sent = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Cafinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373; /* Golden Coffee */
            --secondary-color: #1a1a1a; /* Dark Roast */
            --glass-bg: rgba(0, 0, 0, 0.75);
            --overlay: rgba(0, 0, 0, 0.6);
            --cream: #fefae0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(var(--overlay), var(--overlay)), 
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

        /* --- CONTACT CONTENT --- */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px 80px;
            max-width: 1400px;
            margin: 0 auto;
            gap: 80px;
        }

        .contact-info { flex: 1; max-width: 450px; }

        .contact-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .info-item {
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .info-item span {
            color: var(--primary-color);
            display: block;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        /* --- CONTACT FORM (GLASS CARD) --- */
        .contact-form-card {
            flex: 1;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 50px;
            border-radius: 20px;
            border: 1px solid rgba(212, 163, 115, 0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            max-width: 550px;
        }

        .contact-form-card h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2rem;
            text-transform: uppercase;
        }

        input, textarea {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
        }

        textarea { height: 120px; resize: none; }
        input:focus, textarea:focus { outline: none; border-color: var(--primary-color); }

        .send-btn {
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
        }

        .send-btn:hover { background: var(--cream); transform: translateY(-3px); }

        .success-msg {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
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
            font-size: 1.2rem;
        }

        .footer-section p, .footer-section li { color: #888; font-size: 0.9rem; line-height: 1.8; list-style: none; }

        .footer-bottom {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            font-size: 0.8rem;
            color: #555;
            letter-spacing: 1px;
        }

        @media (max-width: 992px) {
            .main-content { flex-direction: column; padding: 40px; text-align: center; }
            .top-nav { padding: 30px 40px; }
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div class="nav-left">
            <img src="indexpic.jpg" alt="Cafinity Logo">
            <span>CAFINITY</span>
        </div>
        <div class="nav-right">
            <a href="index.php">Home</a>
            <a href="about.php">About</a> <a href="contacts.php" style="color: var(--primary-color);">Contacts</a> <a href="login.php">Login</a>
            <a href="register.php" class="join-btn">Join Us</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="contact-info">
            <h1>Get In Touch</h1>
            <p style="color: #bbb; margin-bottom: 40px;">Have a question about our coffee or your booking? Reach out and we'll get back to you within 24 hours.</p>
            
            <div class="info-item">
                <span>Email Address</span> support@caffinity.com
            </div>
            <div class="info-item">
                <span>Phone Support</span> +63 912 345 6789
            </div>
            <div class="info-item">
                <span>Visit Us</span> 123 Coffee St., Minglanilla, Cebu
            </div>
        </div>

        <div class="contact-form-card">
            <h2>Send Message</h2>
            <?php if ($msg_sent): ?><div class="success-msg">Message sent! We will contact you soon.</div><?php endif; ?>

            <form method="post">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="How can we help?" required></textarea>
                <button type="submit" class="send-btn">Send Message</button>
            </form>
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
                <h3>Support</h3>
                <p>Email: hello@cafinity.com<br>123 Coffee St., Minglanilla</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 CAFINITY COFFEE CO. ALL RIGHTS RESERVED.
        </div>
    </footer>

</body>
</html>