<?php
    session_start();
    require_once '../db_config.php';

    // Redirect to home if not logged in as an attendee
    if (!isset($_SESSION['attendee_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Initrialize success and error messages
    $success_message = "";
    $error_message = "";
    $firstName_error = "";
    $lastName_error = "";
    $email_error = "";

    // Fetch current user data
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['attendee_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Logout
        if (isset($_POST['action']) && $_POST['action'] === 'logout') {
            session_unset();
            session_destroy();
            header("Location: ../index.php");
            exit();
        }

        // Update personal details
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            $firstName = ucwords(trim($_POST['firstName'] ?? '')); // Convert first name to title case and trim whitespace
            $lastName  = ucwords(trim($_POST['lastName']  ?? '')); // Convert last name to title case and trim whitespace
            $email     = trim($_POST['email']     ?? '');
            $password  = $_POST['password']       ?? '';

            // Validation
            if (empty($firstName))  {
                $firstName_error = "First name is required.";
            }
            if (empty($lastName))   {
                $lastName_error  = "Last name is required.";
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_error = "A valid email address is required.";
            }

            if (empty($firstName_error) && empty($lastName_error) && empty($email_error)) {
                $upd = null;  // 
                // Check if email is already taken by another user
                $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $email_check->bind_param("si", $email, $_SESSION['attendee_id']);
                $email_check->execute();
                $email_check->store_result();
                
                if ($email_check->num_rows > 0) {
                    $email_error = "This email is already registered to another account.";
                    $email_check->close();
                } 
                elseif (!empty($password)) {
                    // Update including new password
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, password=? WHERE id=?");
                    $upd->bind_param("ssssi", $firstName, $lastName, $email, $hashed, $_SESSION['attendee_id']);
                } 
                else {
                    // Update without changing password
                    $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
                    $upd->bind_param("sssi", $firstName, $lastName, $email, $_SESSION['attendee_id']);
                }

                if ($upd && $upd->execute()) {  // Check if $upd is actually a statement
                    $success_message = "Details updated successfully.";
                    $user['first_name'] = $firstName;
                    $user['last_name']  = $lastName;
                    $user['email']      = $email;
                }
                if ($upd) {  // Only close if it exists
                    $upd->close();
                }
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
            <!-- Sidebar navigation -->
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
                <!-- Personal Details section -->
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

                        <!-- First Name -->
                        <div class="input-group">
                            <input type="text" id="firstName" name="firstName" placeholder=" " value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            <label for="firstName">First Name</label>
                            <span class="field-error" id="firstName_error">
                                <?php echo htmlspecialchars($firstName_error); ?>
                            </span>
                        </div>

                        <!-- Last Name -->
                        <div class="input-group">
                            <input type="text" id="lastName" name="lastName" placeholder=" " value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            <label for="lastName">Last Name</label>
                            <span class="field-error" id="lastName_error">
                                <?php echo htmlspecialchars($lastName_error); ?>
                            </span>
                        </div>

                        <!-- Email -->
                        <div class="input-group">
                            <input type="email" id="email" name="email" placeholder=" " value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            <label for="email">Email Address</label>
                            <span class="field-error" id="email_error">
                                <?php echo htmlspecialchars($email_error); ?>
                            </span>
                        </div>

                        <!-- New Password (optional) -->
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

                <!-- My Events section -->
                <section id="my-events">
                    <h2 class="section-title">My Events</h2>
                    <p>Here you can see the events you've registered for. (Feature coming soon!)</p>
                </section>

                <!-- Favourites section -->
                <section id="favourites">
                    <h2 class="section-title">Favourites</h2>
                    <p>Here you can see your favourite events and organizers. (Feature coming soon!)</p>
                </section>
            </main>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script src="../js/my_arena.js"></script>
</body>
</html>