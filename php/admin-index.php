<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../php/login.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>

<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArenaSync | Admin Home</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/chatbot.css">
    <script src="https://unpkg.com/globe.gl"></script>
    <script src="../js/chatbot.js" defer></script>
</head>

<body>
    <div id="container">

        <header id="masthead">
            <a href="./admin-index.php">
                <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
            </a>

            <p>ArenaSync (Admin)</p>

            <nav class="navbar">
                <div class="hamburger" id="hamburger">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>

                <ul class="nav-links" id="nav-links">
                    <li><a href="./admin-index.php"><span>Home</span></a></li>
                    <li><a href="./admin-dashboard.php"><span>Dashboard</span></a></li>

                    <li>
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="logout" class="nav-login-btn" style="cursor:pointer;">
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div class="hero-overlay"></div>

                <div id="intro">
                    <h1 class="welcome-heading">Welcome to ArenaSync</h1>

                    <p class="welcome-tagline">
                        "Your arena. Your events. In sync."
                    </p>

                    <a href="./admin-dashboard.php" class="hero-cta">
                        View Admin Panel
                    </a>
                </div>
            </section>

            <section id="about-content">
                <h1>Admin Overview</h1>
            </section>

            <div id="about-description">
                <div>
                    <p>
                        Welcome Admin. From here you can manage users, events, games, and platform statistics.
                    </p>
                    <p>
                        Use the Admin Panel to maintain system integrity and oversee all ArenaSync operations.
                    </p>
                    <p>
                        All actions performed here are logged and affect live platform data.
                    </p>
                </div>
            </div>

            <div id="about-mission-vision">
                <section id="about-mission">
                    <h2>Admin Control</h2>
                    <p>
                        "Manage users, events, and games efficiently through a unified admin system."
                    </p>
                </section>

                <section id="about-vision">
                    <h2>System Oversight</h2>
                    <p>
                        "Ensure platform stability, data consistency, and smooth event operations."
                    </p>
                </section>
            </div>

            <div id="socials-section">
                <h2>Follow Us</h2>
                <div id="socials-counter-container">
                    <div class="social-item">
                        <img src="../icons/facebook-app-symbol.png" class="social-icon" alt="Facebook">
                        <span class="home-counter" data-target="151000">0</span>
                        <p class="social-label">Facebook Followers</p>
                    </div>
                    <div class="social-item">
                        <img src="../icons/twitter-black-shape.png" class="social-icon" alt="Twitter">
                        <span class="home-counter" data-target="122300">0</span>
                        <p class="social-label">Twitter Followers</p>
                    </div>
                    <div class="social-item">
                        <img src="../icons/twitch.png" class="social-icon" alt="Twitch">
                        <span class="home-counter" data-target="89500">0</span>
                        <p class="social-label">Twitch Followers</p>
                    </div>
                    <div class="social-item">
                        <img src="../icons/youtube.png" class="social-icon" alt="YouTube">
                        <span class="home-counter" data-target="2080000">0</span>
                        <p class="social-label">YouTube Subscribers</p>
                    </div>
                </div>
            </div>

            <div id="locations">
                <section id="globe-section">
                    <h2>Our Locations</h2>
                    <div id="globe-container"></div>
                </section>
                <section id="offices">
                    <div id="headquarters">
                        <h2>Headquarters</h2>
                        <p>ArenaSync is headquartered at Griffith College, Cork, Ireland.</p>
                        <p><strong>Address:</strong> Griffith College Cork, Wellington Road, Cork City, Co. Cork, Ireland</p>
                        <p><strong>Contact:</strong> +353 (12) 345 6789</p>
                    </div>
                    <br>
                    <div id="branch-offices">
                        <h2>Event Hubs</h2>
                        <ul>
                            <li><strong>Dublin:</strong> 123 Dublin Street, Dublin, Ireland</li>
                            <li><strong>Lagos:</strong> 456 Lagos Avenue, Lagos, Nigeria</li>
                            <li><strong>Tokyo:</strong> 789 Tokyo Road, Tokyo, Japan</li>
                            <li><strong>Los Angeles:</strong> 101 Hollywood Blvd, Los Angeles, USA</li>
                            <li><strong>Seoul:</strong> 234 Seoul Street, Seoul, South Korea</li>
                            <li><strong>São Paulo:</strong> 567 São Paulo Avenue, São Paulo, Brazil</li>
                            <li><strong>Sydney:</strong> 890 Sydney Road, Sydney, Australia</li>
                            <li><strong>McMurdo Station:</strong> 123 Antarctic Road, Antarctica</li>
                        </ul>
                    </div>
                </section>
            </div>

        </main>

        <footer id="footer">
            <div class="footer-socials">
                <a href="#" aria-label="Facebook"><img src="../icons/facebook-app-symbol.png" class="footer-icon" alt="Facebook"></a>
                <a href="#" aria-label="Twitter"><img src="../icons/twitter-black-shape.png" class="footer-icon" alt="Twitter"></a>
                <a href="#" aria-label="Twitch"><img src="../icons/twitch.png" class="footer-icon" alt="Twitch"></a>
                <a href="#" aria-label="YouTube"><img src="../icons/youtube.png" class="footer-icon" alt="YouTube"></a>
            </div>

            <div class="footer-links">
                <a href="#" class="footer-link">Support</a>
                <a href="#" class="footer-link">System Logs</a>
            </div>

            <p>&copy; 2026 ArenaSync Admin Panel</p>
            <p class="footer-credits">System Administrator Access</p>
        </footer>

    </div>

    <script src="../js/home.js"></script>
</body>

</html>