<?php
    session_start();
    require_once '../db_config.php';
    require_once 'remember-me.php';
    if (!isset($_SESSION['attendee_id'])) {
        header("Location: ./login.php");
        exit();
    }

    $attendee_id = (int) $_SESSION['attendee_id'];

    // All events with game and organiser info
    $stmt = $conn->prepare("
        SELECT e.id, e.date_time,
               g.name AS game_name, g.description AS game_description,
               u.company
        FROM events e
        JOIN games g ON e.game_id = g.id
        JOIN users u ON e.organiser_id = u.id
        ORDER BY e.date_time ASC
    ");
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Already-booked event IDs
    $booked_ids = [];
    $bk = $conn->prepare("SELECT event_id FROM bookings WHERE user_id = ?");
    $bk->bind_param("i", $attendee_id);
    $bk->execute();
    $bk_res = $bk->get_result();
    while ($r = $bk_res->fetch_assoc()) { $booked_ids[] = (int) $r['event_id']; }
    $bk->close();

    // Favourite event IDs
    $fav_event_ids = [];
    $fav = $conn->prepare("SELECT event_id FROM favourite_events WHERE attendee_id = ?");
    $fav->bind_param("i", $attendee_id);
    $fav->execute();
    $fav_res = $fav->get_result();
    while ($r = $fav_res->fetch_assoc()) { $fav_event_ids[] = (int) $r['event_id']; }
    $fav->close();

    $game_images = [
        'EA Sports FC'        => 'EA-FC-25-Premier-League-POTM-.avif',
        'Batman'              => 'Batman.jpg',
        'Apex Legends'        => 'apex-legends-background.png',
        'NBA 2K'              => 'NBA2k25.jpg',
        'Call of Duty'        => 'callofduty.jpg',
        'Fortnite'            => 'fortnite.png',
        'Grand Theft Auto'    => 'gta-background.webp',
        'Mortal Kombat'       => 'mortal-kombat.jpg',
        'Zelda'               => 'zelda.jpg',
        'Forza'               => 'forza-background.jpg',
    ];

    function getGameImage(string $name, array $map): string {
        foreach ($map as $key => $img) {
            if (stripos($name, $key) !== false) return $img;
        }
        return 'apex-legends-background.png';
    }

    $cal_svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/><path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>';
    $info_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="m9.708 6.075-3.024.379-.108.502.595.108c.387.093.464.232.38.619l-.975 4.577c-.255 1.183.14 1.74 1.067 1.74.72 0 1.554-.332 1.933-.789l.116-.549c-.263.232-.65.325-.905.325-.363 0-.494-.255-.402-.704zm.091-2.755a1.32 1.32 0 1 1-2.64 0 1.32 1.32 0 0 1 2.64 0"/></svg>';
    $star_e   = '<svg class="star-empty" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.56.56 0 0 0-.163-.505L1.71 6.745l4.052-.576a.53.53 0 0 0 .393-.288L8 2.223l1.847 3.658a.53.53 0 0 0 .393.288l4.052.575-2.906 2.77a.56.56 0 0 0-.163.506l.694 3.957-3.686-1.894a.5.5 0 0 0-.461 0z"/></svg>';
    $star_f   = '<svg class="star-filled" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>';
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArenaSync | Events</title>
    <link rel="stylesheet" href="../css/events.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/chatbot.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script src="../js/events.js" defer></script>
    <script src="../js/chatbot.js" defer></script>
</head>

<body>
    <div class="container">
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
                    <?php if(isset($_SESSION['attendee_id'])): ?>
                        <li><a href="./events.php"><span>Events</span></a></li>
                        <li><a href="./organizers.php"><span>Organizers</span></a></li>
                        <li id="my-arena-link"><a href="./my_arena.php"><span><i>MyArena</i></span></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <div class="events-welcome-page">
                <img src="../images/backgrounds/apex-legends-background.png" class="events-background"
                    alt="Event Background">
                <div class="discover-events">
                    <p>Upcoming Events</p>
                    <a href="#event-list" class="discover-events-link">
                        <svg xmlns="http://www.w3.org/2000/svg" class="discover-events-arrow" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M1.646 6.646a.5.5 0 0 1 .708 0L8 12.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                            <path fill-rule="evenodd"
                                d="M1.646 2.646a.5.5 0 0 1 .708 0L8 8.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                        </svg>
                    </a>
                </div>
            </div>

            <div id="event-list">
                <?php if (empty($events)): ?>
                    <p class="no-events-message">No events are scheduled at the moment. Check back soon!</p>
                <?php else: ?>

                    <?php foreach ($events as $ev):
                        $img      = getGameImage($ev['game_name'], $game_images);
                        $popupId  = 'popup-event-' . $ev['id'];
                        $isFav    = in_array((int) $ev['id'], $fav_event_ids);
                        $isBooked = in_array((int) $ev['id'], $booked_ids);
                        $date     = date('F j, Y', strtotime($ev['date_time']));
                        $time     = date('g:i A', strtotime($ev['date_time'])) . ' GMT';
                    ?>
                    <div class="event-card" data-event-id="<?php echo $ev['id']; ?>">
                        <img src="../images/backgrounds/<?php echo htmlspecialchars($img); ?>"
                             alt="<?php echo htmlspecialchars($ev['game_name']); ?> Event">
                        <section class="event-details">
                            <div class="event-card-top">
                                <h2><?php echo htmlspecialchars($ev['game_name']); ?></h2>
                                <button class="fav-btn<?php echo $isFav ? ' favourited' : ''; ?>"
                                        data-event-id="<?php echo (int) $ev['id']; ?>"
                                        aria-label="<?php echo $isFav ? 'Remove from favourites' : 'Add to favourites'; ?>"
                                        type="button">
                                    <?php echo $star_e . $star_f; ?>
                                </button>
                            </div>
                            <p class="event-company"><?php echo htmlspecialchars($ev['company']); ?></p>
                            <p class="event-date">
                                <?php echo $cal_svg; ?><br>
                                <?php echo htmlspecialchars($date . ' – ' . $time); ?>
                            </p>
                            <a href="#<?php echo $popupId; ?>" class="learn-more"
                               data-popup-id="<?php echo $popupId; ?>">
                                <?php echo $info_svg; ?>
                                Learn More
                            </a>
                        </section>
                    </div>
                    <?php endforeach; ?>

                    <?php foreach ($events as $ev):
                        $img      = getGameImage($ev['game_name'], $game_images);
                        $popupId  = 'popup-event-' . $ev['id'];
                        $isBooked = in_array((int) $ev['id'], $booked_ids);
                        $date     = date('F j, Y', strtotime($ev['date_time']));
                        $time     = date('g:i A', strtotime($ev['date_time'])) . ' GMT';
                        $desc     = !empty($ev['game_description'])
                                    ? $ev['game_description']
                                    : 'Join us for an exciting gaming event featuring ' . $ev['game_name'] . '!';
                    ?>
                    <section id="<?php echo $popupId; ?>" class="popup"
                             data-event-id="<?php echo $ev['id']; ?>"
                             data-event-title="<?php echo htmlspecialchars($ev['game_name'], ENT_QUOTES); ?>"
                             data-event-date="<?php echo htmlspecialchars($date, ENT_QUOTES); ?>"
                             data-event-time="<?php echo htmlspecialchars($time, ENT_QUOTES); ?>"
                             data-event-company="<?php echo htmlspecialchars($ev['company'], ENT_QUOTES); ?>">
                        <button class="close-popup">X</button>
                        <img src="../images/backgrounds/<?php echo htmlspecialchars($img); ?>"
                             alt="<?php echo htmlspecialchars($ev['game_name']); ?> Event">
                        <h2><?php echo htmlspecialchars($ev['game_name']); ?></h2>
                        <p class="event-company"><?php echo htmlspecialchars($ev['company']); ?></p>
                        <p class="event-date">
                            <?php echo $cal_svg; ?>
                            <?php echo htmlspecialchars($date . ' – ' . $time . ' | Online'); ?>
                        </p>
                        <p><?php echo htmlspecialchars($desc); ?></p>
                        <?php if ($isBooked): ?>
                            <span class="already-registered">&#10003; You&rsquo;re registered for this event</span>
                        <?php else: ?>
                            <a href="#" class="event-link">RSVP Now</a>
                        <?php endif; ?>
                    </section>
                    <?php endforeach; ?>

                <?php endif; ?>
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
                <a href="./support.php" class="footer-link">Support</a>
                <a href="#" class="footer-link">Terms of Service</a>
            </div>
            <p>&copy; 2026 ArenaSync</p>
            <p class="footer-credits">Emmanuel &nbsp;&nbsp; Ahmad &nbsp;&nbsp; Miguel</p>
        </footer>
        <div id="dim-overlay"></div>
    </div>
</body>

</html>
