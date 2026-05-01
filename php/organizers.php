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

    // Fetch the IDs of organizers already favourited by this attendee
    $fav_ids = [];
    $fav_stmt = $conn->prepare("SELECT organizer_id FROM favourite_organizers WHERE attendee_id = ?");
    $fav_stmt->bind_param("i", $_SESSION['attendee_id']);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    while ($row = $fav_result->fetch_assoc()) {
        $fav_ids[] = $row['organizer_id'];
    }
    $fav_stmt->close();
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
                    <?php foreach ($organizers as $organizer):
                        $isFav = in_array($organizer['id'], $fav_ids);
                    ?>
                        <div class="organizer-card">
                            <div class="organizer-card-body">
                                <div class="organizer-card-top">
                                    <h5><?php echo htmlspecialchars($organizer['company']); ?></h5>
                                    <button class="fav-btn<?php echo $isFav ? ' favourited' : ''; ?>"
                                            data-organizer-id="<?php echo (int) $organizer['id']; ?>"
                                            aria-label="<?php echo $isFav ? 'Remove from favourites' : 'Add to favourites'; ?>"
                                            type="button">
                                        <svg class="star-empty" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.56.56 0 0 0-.163-.505L1.71 6.745l4.052-.576a.53.53 0 0 0 .393-.288L8 2.223l1.847 3.658a.53.53 0 0 0 .393.288l4.052.575-2.906 2.77a.56.56 0 0 0-.163.506l.694 3.957-3.686-1.894a.5.5 0 0 0-.461 0z"/>
                                        </svg>
                                        <svg class="star-filled" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                        </svg>
                                    </button>
                                </div>
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
    <script>
        document.querySelectorAll('.fav-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const organizerId = this.dataset.organizerId;
                try {
                    const res = await fetch('./toggle_favourite_organizer.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'organizer_id=' + encodeURIComponent(organizerId)
                    });
                    const data = await res.json();
                    if (data.favourited) {
                        this.classList.add('favourited');
                        this.setAttribute('aria-label', 'Remove from favourites');
                    } else {
                        this.classList.remove('favourited');
                        this.setAttribute('aria-label', 'Add to favourites');
                    }
                } catch (err) {
                    console.error('Failed to toggle favourite:', err);
                }
            });
        });
    </script>
</body>
</html>