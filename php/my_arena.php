<?php
    session_start();
    require_once '../db_config.php';
    require_once 'remember-me.php';

    if (!isset($_SESSION['attendee_id'])) {
        header("Location: ../index.php");
        exit();
    }

    $success_message = "";
    $error_message   = "";
    $firstName_error = "";
    $lastName_error  = "";
    $email_error     = "";

    // Fetch current user data
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['attendee_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch booked events
    $booked_events = [];
    $bk_stmt = $conn->prepare("
        SELECT e.id, e.date_time, g.name AS game_name, u.company
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        JOIN games g  ON e.game_id  = g.id
        JOIN users u  ON e.organiser_id = u.id
        WHERE b.user_id = ?
        ORDER BY e.date_time ASC
    ");
    $bk_stmt->bind_param("i", $_SESSION['attendee_id']);
    $bk_stmt->execute();
    $booked_events = $bk_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $bk_stmt->close();

    // Fetch favourite events
    $fav_events = [];
    $fev_stmt = $conn->prepare("
        SELECT e.id, e.date_time, g.name AS game_name, u.company
        FROM favourite_events fe
        JOIN events e ON fe.event_id = e.id
        JOIN games g  ON e.game_id   = g.id
        JOIN users u  ON e.organiser_id = u.id
        WHERE fe.attendee_id = ?
        ORDER BY fe.created_at DESC
    ");
    $fev_stmt->bind_param("i", $_SESSION['attendee_id']);
    $fev_stmt->execute();
    $fav_events = $fev_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $fev_stmt->close();

    // Fetch favourite organizers
    $fav_orgs = [];
    $fav_stmt = $conn->prepare("
        SELECT u.id, u.company
        FROM favourite_organizers fo
        JOIN users u ON fo.organizer_id = u.id
        WHERE fo.attendee_id = ?
        ORDER BY fo.created_at DESC
    ");
    $fav_stmt->bind_param("i", $_SESSION['attendee_id']);
    $fav_stmt->execute();
    $fav_orgs = $fav_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $fav_stmt->close();

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['action']) && $_POST['action'] === 'logout') {
            if (isset($_COOKIE['remember_me'])) {
                $clr = $conn->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
                $clr->bind_param("s", $_COOKIE['remember_me']);
                $clr->execute();
                $clr->close();
                setcookie('remember_me', '', time() - 3600, "/");
            }
            session_unset();
            session_destroy();
            header("Location: ../index.php");
            exit();
        }

        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            $firstName = ucwords(trim($_POST['firstName'] ?? ''));
            $lastName  = ucwords(trim($_POST['lastName']  ?? ''));
            $email     = trim($_POST['email']     ?? '');
            $password  = $_POST['password']       ?? '';

            if (empty($firstName)) $firstName_error = "First name is required.";
            if (empty($lastName))  $lastName_error  = "Last name is required.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_error = "A valid email address is required.";
            }

            if (empty($firstName_error) && empty($lastName_error) && empty($email_error)) {
                $upd = null;
                $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $email_check->bind_param("si", $email, $_SESSION['attendee_id']);
                $email_check->execute();
                $email_check->store_result();

                if ($email_check->num_rows > 0) {
                    $email_error = "That email is already registered to another account.";
                    $email_check->close();
                } elseif (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, password=? WHERE id=?");
                    $upd->bind_param("ssssi", $firstName, $lastName, $email, $hashed, $_SESSION['attendee_id']);
                } else {
                    $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
                    $upd->bind_param("sssi", $firstName, $lastName, $email, $_SESSION['attendee_id']);
                }

                if ($upd && $upd->execute()) {
                    $success_message = "Details updated successfully.";
                    $user['first_name'] = $firstName;
                    $user['last_name']  = $lastName;
                    $user['email']      = $email;
                }
                if ($upd) $upd->close();
            }
        }
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
                    <?php if(isset($_SESSION['attendee_id'])): ?>
                        <li><a href="./events.php"><span>Events</span></a></li>
                        <li><a href="./organizers.php"><span>Organizers</span></a></li>
                        <li id="my-arena-link"><a href="./my_arena.php"><span><i>MyArena</i></span></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <div id="main-content">
            <sidebar id="sidebar">
                <ul>
                    <li><a href="#personal-details" class="active">Personal Details</a></li>
                    <li><a href="#my-events">My Events</a></li>
                    <li><a href="#favourites">Favourites</a></li>
                </ul>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button class="logout-btn" type="submit">Logout</button>
                </form>
            </sidebar>

            <main id="main">
                <!-- Personal Details -->
                <section id="personal-details">
                    <h2 class="section-title">Personal Details</h2>

                    <?php if (!empty($success_message)): ?>
                        <p class="form-success"><?php echo htmlspecialchars($success_message); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <p class="form-error"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>

                    <form id="personalDetailsForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
                        <input type="hidden" name="action" value="update">

                        <div class="input-group">
                            <input type="text" id="firstName" name="firstName" placeholder=" "
                                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            <label for="firstName">First Name</label>
                            <span class="field-error" id="firstName_error"><?php echo htmlspecialchars($firstName_error); ?></span>
                        </div>

                        <div class="input-group">
                            <input type="text" id="lastName" name="lastName" placeholder=" "
                                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            <label for="lastName">Last Name</label>
                            <span class="field-error" id="lastName_error"><?php echo htmlspecialchars($lastName_error); ?></span>
                        </div>

                        <div class="input-group">
                            <input type="email" id="email" name="email" placeholder=" "
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            <label for="email">Email Address</label>
                            <span class="field-error" id="email_error"><?php echo htmlspecialchars($email_error); ?></span>
                        </div>

                        <div class="input-group">
                            <input type="password" id="password" name="password" placeholder=" ">
                            <label for="password">New Password <span class="label-hint">(leave blank to keep current)</span></label>
                            <span id="togglePassword" class="toggle-password" aria-label="Toggle password visibility">
                                Show Password
                            </span>
                        </div>

                        <button type="submit" class="btn">Update</button>
                    </form>
                </section>

                <!-- My Events -->
                <section id="my-events">
                    <h2 class="section-title">My Events</h2>
                    <?php if (empty($booked_events)): ?>
                        <p class="fav-empty">You haven't registered for any events yet.
                            <a href="./events.php">Browse events</a> to get started!
                        </p>
                    <?php else: ?>
                        <div class="booked-events-grid">
                            <?php foreach ($booked_events as $ev): ?>
                                <div class="booked-event-card">
                                    <h5><?php echo htmlspecialchars($ev['game_name']); ?></h5>
                                    <p class="booked-event-company"><?php echo htmlspecialchars($ev['company']); ?></p>
                                    <p class="booked-event-date">
                                        <?php echo htmlspecialchars(date('F j, Y', strtotime($ev['date_time']))); ?>
                                        &mdash;
                                        <?php echo htmlspecialchars(date('g:i A', strtotime($ev['date_time']))); ?> GMT
                                    </p>
                                    <span class="registered-badge">&#10003; Registered</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Favourites -->
                <section id="favourites">
                    <h2 class="section-title">Favourite Events</h2>
                    <?php if (empty($fav_events)): ?>
                        <p class="fav-empty">No favourite events yet.
                            <a href="./events.php">Browse events</a> and star the ones you love!
                        </p>
                    <?php else: ?>
                        <div class="fav-orgs-grid">
                            <?php foreach ($fav_events as $ev): ?>
                                <div class="fav-org-card">
                                    <h5><?php echo htmlspecialchars($ev['game_name']); ?></h5>
                                    <p class="booked-event-company"><?php echo htmlspecialchars($ev['company']); ?></p>
                                    <ul class="fav-org-events">
                                        <li><?php echo htmlspecialchars(date('F j, Y', strtotime($ev['date_time']))); ?>
                                            &mdash;
                                            <?php echo htmlspecialchars(date('g:i A', strtotime($ev['date_time']))); ?> GMT
                                        </li>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h2 class="section-title" style="margin-top: 2rem;">Favourite Organizers</h2>
                    <?php if (count($fav_orgs) > 0): ?>
                        <div class="fav-orgs-grid">
                            <?php foreach ($fav_orgs as $org): ?>
                                <div class="fav-org-card">
                                    <h5><?php echo htmlspecialchars($org['company']); ?></h5>
                                    <ul class="fav-org-events">
                                        <?php
                                            $ev_stmt = $conn->prepare("SELECT g.name FROM events e JOIN games g ON e.game_id = g.id WHERE e.organiser_id = ?");
                                            $ev_stmt->bind_param("i", $org['id']);
                                            $ev_stmt->execute();
                                            $ev_result = $ev_stmt->get_result();
                                            if ($ev_result->num_rows > 0) {
                                                while ($ev = $ev_result->fetch_assoc()) {
                                                    echo "<li>" . htmlspecialchars($ev['name']) . "</li>";
                                                }
                                            } else {
                                                echo "<li>No events yet</li>";
                                            }
                                            $ev_stmt->close();
                                        ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="fav-empty">You haven't favourited any organizers yet.
                            <a href="./organizers.php">Browse organizers</a> to add some!
                        </p>
                    <?php endif; ?>
                </section>
            </main>
        </div>

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
    <script src="../js/my_arena.js"></script>
</body>
</html>
