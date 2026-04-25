<?php
    // Start the session to manage user state across pages
    session_start();
    
    // Include the database connection and attendee-related functions
    require_once '../db_config.php';

    // Check if the user is logged in as an attendee; if not, redirect to the home page
    if (!isset($_SESSION['attendee_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Handle logout request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         // Clear all session data to log out the user
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArenaSync | My Arena</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/my_arena.css">
</head>

<body>
    <div id="container">
        <header id="masthead">
            <a href="../index.php">
                <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
            </a>
            <p>ArenaSync</p>
            <nav class="navbar">
                <div class="hamburger" id="hamburger">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <ul class="nav-links" id="nav-links">
                    <li><a href="../index.php"><span>Home</span></a></li>
                    <!-- Show events, organizers, and my arena links only if an attendee is logged in -->
                    <?php if(isset($_SESSION['attendee_id'])): ?>
                        <li><a href="./events.php"><span>Events</span></a></li>
                        <li><a href="./organizers.php"><span>Organizers</span></a></li>
                        <li id="my-arena-link"><a href="./my_arena.php"><span><i>MyArena</i></span></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <div id="main-content">
            <!-- Sidebar for My Arena navigation -->
            <sidebar id="sidebar">
                <ul>
                    <li><a href="./my_arena.php">Personal Details</a></li>
                    <li><a href="./my_profile.php">My Events</a></li>
                    <li><a href="./logout.php">Favourites</a></li>
                </ul>

                <!-- Logout button, clear all session data and redirect to home page -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <button class="logout-btn" type="submit">Logout</button>
                </form>
            </sidebar>
            <main id="main">
            </main>
        </div>

    </div>

    <!-- JavaScript for responsive navigation will go here -->
    <script src="../js/main.js"></script>
</body>
</html>