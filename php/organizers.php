<?php
    // Start the session to manage user state across pages
    session_start();
    // Include the database connection and organizer-related functions
    require_once '../db_config.php';
    require_once 'remember-me.php';

    // Check if the user is logged in as an attendee; if not, redirect to the login page
    if (!isset($_SESSION['attendee_id'])) {
        header("Location: ./login.php");
        exit();
    }

    // Get the organization name from the search query if it exists, otherwise set it to an empty string
    $organization_name = trim($_GET['org_search'] ?? '');

    // Always fetch organizers: filter by name if a search was submitted, otherwise return all
    if (!empty($organization_name)) {
        $search = "%$organization_name%";
        $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'organiser' AND company LIKE ?");
        $stmt->bind_param("s", $search);
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'organiser'");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $organizers = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArenaSync | Organizers</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/organizers.css">
    <link rel="stylesheet" href="../css/chatbot.css">
    <script src="../js/chatbot.js" defer></script>
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

        <main>
            <form id="org_search" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" novalidate>
                <div class="search-wrapper">
                    <!-- The search input retains the user's query after submission for better UX -->
                    <input type="text" name="org_search" placeholder="Search organizers..." value="<?php echo isset($_GET['org_search']) ? htmlspecialchars($_GET['org_search']) : ''; ?>">
                    <button type="submit">Search</button>
                </div>
            </form>

            <!-- Display organizer cards if any exist; otherwise show an empty-state message -->
            <?php if (count($organizers) > 0): ?>
                <div class="organizers-grid">
                    <!-- Loop through each organizer and display their company name and events -->
                    <?php foreach ($organizers as $organizer): ?>
                        <div class="organizer-card">
                            <div class="organizer-card-body">
                                <h5><?php echo htmlspecialchars($organizer['company']); ?></h5>
                                <ul class="organizer-events">
                                    <?php
                                        // Fetch events organised by the current organizer
                                        $events_sql = "SELECT g.name FROM events e JOIN games g ON e.game_id = g.id WHERE e.organiser_id = ?";
                                        $events_stmt = $conn->prepare($events_sql);
                                        $events_stmt->bind_param("i", $organizer['id']);
                                        $events_stmt->execute();
                                        $events_result = $events_stmt->get_result();

                                        // If the organizer has events, list them; otherwise indicate there are no events
                                        if ($events_result->num_rows > 0) {
                                            while ($event = $events_result->fetch_assoc()) {
                                                echo "<li>" . htmlspecialchars($event['name']) . "</li>";
                                            }
                                        } else {
                                            echo "<li>No events yet, check back soon!</li>";
                                        }
                                        $events_stmt->close();
                                    ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="organizers-empty">No organizers found.</p>
            <?php endif; ?>
        </main>

        <footer id="footer">
            <div class="footer-socials">
                <a href="#" aria-label="Facebook"><img src="../icons/facebook-app-symbol.png" class="footer-icon" alt="Facebook"></a>
                <a href="#" aria-label="Twitter"><img src="../icons/twitter-black-shape.png" class="footer-icon" alt="Twitter"></a>
                <a href="#" aria-label="Twitch"><img src="../icons/twitch.png" class="footer-icon" alt="Twitch"></a>
                <a href="#" aria-label="YouTube"><img src="../icons/youtube.png" class="footer-icon" alt="YouTube"></a>
            </div>
            <div class="footer-links">
                <a href="./support.php" class="footer-link">Support</a>
                <a href="#" class="footer-link">Terms of Service</a>
            </div>
            <p>&copy; 2026 ArenaSync</p>
            <p class="footer-credits">Emmanuel &nbsp;&nbsp; Ahmad &nbsp;&nbsp; Miguel</p>
        </footer>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>