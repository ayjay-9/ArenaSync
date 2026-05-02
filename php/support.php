<?php
session_start();
require_once '../db_config.php';
require_once 'remember-me.php';
require_once 'services/email_service.php';

if (!isset($_SESSION['attendee_id']) && !isset($_SESSION['organizer_id'])) {
    header("Location: ../index.php");
    exit();
}

$is_attendee = isset($_SESSION['attendee_id']);
$user_id     = $is_attendee ? (int) $_SESSION['attendee_id'] : (int) $_SESSION['organizer_id'];

if ($is_attendee) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT company, email FROM users WHERE id = ?");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$success_message = '';
$error_message   = '';

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

    $ticket  = trim($_POST['ticket']  ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($ticket) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $from_email = $user['email'];
        $from_name  = $is_attendee
            ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])
            : htmlspecialchars($user['company']);

        if (send_support_email($from_name, $from_email, $ticket, $message)) {
            $success_message = "Your message has been sent. We'll get back to you soon!";
        } else {
            $error_message = "Failed to send your message. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArenaSync | Support</title>
  <link rel="stylesheet" href="../css/support.css">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/chatbot.css">
  <script src="../js/chatbot.js" defer></script>
</head>

<body>
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
      <?php if ($is_attendee): ?>
      <ul class="nav-links" id="nav-links">
        <li><a href="../index.php"><span>Home</span></a></li>
        <li><a href="./events.php"><span>Events</span></a></li>
        <li><a href="./organizers.php"><span>Organizers</span></a></li>
        <li id="my-arena-link"><a href="./my_arena.php"><span><i>MyArena</i></span></a></li>
        <li><a href="./support.php"><span>Support</span></a></li>
      </ul>
      <?php else: ?>
      <ul class="nav-links" id="nav-links">
        <li><a href="../index.php"><span>Home</span></a></li>
        <li><a href="./organizer-dashboard.php"><span>Dashboard</span></a></li>
        <li><a href="./support.php"><span>Support</span></a></li>
        <li>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="nav-login-btn">Logout</button>
          </form>
        </li>
      </ul>
      <?php endif; ?>
    </nav>
  </header>

  <div class="container">
    <div id="counter-container">
      <div>
        <svg class="socials" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
          <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
        </svg>
        <span class="counter" data-target="151000">151000</span>
      </div>

      <div>
        <svg class="socials" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
          <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334q.002-.211-.006-.422A6.7 6.7 0 0 0 16 3.542a6.7 6.7 0 0 1-1.889.518 3.3 3.3 0 0 0 1.447-1.817 6.5 6.5 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.32 9.32 0 0 1-6.767-3.429 3.29 3.29 0 0 0 1.018 4.382A3.3 3.3 0 0 1 .64 6.575v.045a3.29 3.29 0 0 0 2.632 3.218 3.2 3.2 0 0 1-.865.115 3 3 0 0 1-.614-.057 3.28 3.28 0 0 0 3.067 2.277A6.6 6.6 0 0 1 .78 13.58a6 6 0 0 1-.78-.045A9.34 9.34 0 0 0 5.026 15" />
        </svg>
        <span class="counter" data-target="122300">122300</span>
      </div>

      <div class="youtube-page">
        <svg class="socials" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
          <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z" />
        </svg>
        <span class="counter" data-target="2080000">20800</span>
      </div>

      <div class="linkedin-page">
        <svg class="socials" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
          <path d="M3.857 0 1 2.857v10.286h3.429V16l2.857-2.857H9.57L14.714 8V0zm9.714 7.429-2.285 2.285H9l-2 2v-2H4.429V1.143h9.142z" />
          <path d="M11.857 3.143h-1.143V6.57h1.143zm-3.143 0H7.571V6.57h1.143z" />
        </svg>
        <span class="counter" data-target="454507">454507</span>
      </div>
    </div>

    <h2>Contact Us</h2>

    <?php if (!empty($success_message)): ?>
      <div id="support-success">
        <h3>Message Sent!</h3>
        <p><?php echo htmlspecialchars($success_message); ?></p>
        <a href="./support.php" class="btn">Send Another Message</a>
      </div>
    <?php else: ?>
      <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
      <?php endif; ?>

      <form id="contactForm" method="POST" action=""
            data-role="<?php echo $is_attendee ? 'attendee' : 'organizer'; ?>">

        <?php if ($is_attendee): ?>
        <div class="input-group">
          <input type="text" id="firstName" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly>
          <label for="firstName">First Name</label>
        </div>
        <div class="input-group">
          <input type="text" id="lastName" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>
          <label for="lastName">Last Name</label>
        </div>
        <?php else: ?>
        <div class="input-group">
          <input type="text" id="firstName" value="<?php echo htmlspecialchars($user['company']); ?>" readonly>
          <label for="firstName">Organization Name</label>
        </div>
        <?php endif; ?>

        <div class="input-group">
          <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
          <label for="email">Email Address</label>
        </div>

        <div class="input-group">
          <select id="ticket" name="ticket" required>
            <option value="">-- Select Ticket Enquiry --</option>
            <?php if ($is_attendee): ?>
            <option value="General">General</option>
            <option value="Booking Issue">Booking Issue</option>
            <option value="Account Issue">Account Issue</option>
            <option value="Technical Support">Technical Support</option>
            <option value="Feedback">Feedback</option>
            <?php else: ?>
            <option value="General">General</option>
            <option value="Event Management">Event Management</option>
            <option value="Account Issue">Account Issue</option>
            <option value="Technical Support">Technical Support</option>
            <option value="Feedback">Feedback</option>
            <?php endif; ?>
          </select>
        </div>

        <div class="input-group">
          <textarea id="message" name="message" required></textarea>
          <label for="message">Message</label>
        </div>

        <button type="submit" class="btn">Submit</button>
        <p class="status" id="statusMsg"></p>
      </form>

      <div id="previewContainer" class="hidden">
        <h3>Review Your Details</h3>
        <div id="previewContent"></div>
        <button id="submitFinalBtn" class="btn success">Submit Final</button>
        <button id="editBtn" class="btn secondary">Edit</button>
        <button id="deleteBtn" class="btn danger">Delete</button>
      </div>
    <?php endif; ?>

    <div id="message-container"></div>
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

  <script src="../js/support.js?v=2"></script>
</body>

</html>
