<?php
session_start();

/**
 * PROTECT PAGE
 */
if (!isset($_SESSION['admin_id'])) {
    header("Location: /ArenaSync/php/admin-login.php");
    exit();
}

/**
 * LOGOUT HANDLER
 */
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/ArenaSync/db_config.php";

/* TOTAL USERS */
$stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();
$totalUsers = $stmt->fetchColumn();

/* TOTAL EVENTS */
$stmt = $conn->prepare("SELECT COUNT(*) FROM events");
$stmt->execute();
$totalEvents = $stmt->fetchColumn();

/* TOTAL GAMES */
$stmt = $conn->prepare("SELECT COUNT(*) FROM games");
$stmt->execute();
$totalGames = $stmt->fetchColumn();

/* TOTAL BOOKINGS */
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings");
$stmt->execute();
$totalBookings = $stmt->fetchColumn();
?>

<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ArenaSync | Statistics</title>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/home.css">
<link rel="stylesheet" href="../css/admin.css">
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

            <li>
                <a href="./admin-index.php">
                    <span>Home</span>
                </a>
            </li>

            <li>
                <a href="./admin-dashboard.php">
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout" class="nav-login-btn">
                        <span>Logout</span>
                    </button>
                </form>
            </li>

        </ul>

    </nav>
</header>

<div id="main-content">

<aside id="sidebar">

    <div class="sidebar-header">
        <h3>Admin Panel</h3>
    </div>

    <ul class="sidebar-menu">
        <li><a href="./admin-dashboard.php">Manage Users</a></li>
        <li><a href="./admin-manage-events.php">Manage Events</a></li>
        <li><a href="./admin-manage-games.php">Manage Games</a></li>
        <li><a class="active" href="./admin-statistics.php">View Statistics</a></li>
    </ul>

</aside>

<main id="main">

<h2 class="section-title">Platform Statistics</h2>

<div class="stats-grid">

    <div class="stat-box">
        <h3>Total Users</h3>
        <div class="stat-number"><?= $totalUsers ?></div>
    </div>

    <div class="stat-box">
        <h3>Total Events</h3>
        <div class="stat-number"><?= $totalEvents ?></div>
    </div>

    <div class="stat-box">
        <h3>Total Games</h3>
        <div class="stat-number"><?= $totalGames ?></div>
    </div>

    <div class="stat-box">
        <h3>Total Bookings</h3>
        <div class="stat-number"><?= $totalBookings ?></div>
    </div>

</div>

</main>

</div>

<footer id="footer">
    <p>&copy; 2026 ArenaSync Admin</p>
</footer>

</div>

</body>
</html>