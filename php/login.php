<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArenaSync | Login</title>
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/login.css">
</head>

<body>

  <?php
    // Access the database
    require_once '../db_config.php';
    require_once 'services/email_service.php';

    // Set error message variables for the login form
    $email_error = "";
    $password_error = "";

    // Define minimal allowed personal email domains using a regex pattern (gmail, yahoo, outlook, icloud, and duck)
    $allowed_domains = 'gmail\.com|yahoo\.com|outlook\.com|icloud\.com|duck\.com';

    // Validate the email and password
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
      $role = $_POST['userRole']; // Get the user role from the hidden input field
      $email = trim($_POST["email"]);
      $password = $_POST["password"];

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format";
      }
      // If it's a valid email but it's not a personal email, show an error message
      elseif (!preg_match("/($allowed_domains)$/", $email)) {
        $email_error = "Please use a personal email address";
      }

      // Check if the password is at least 8 characters long
      if (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long";
      }

      // If there are no validation errors, proceed with authentication
      if (empty($email_error) && empty($password_error)) {
        // Prepare a SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, email, password, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
          $user = $result->fetch_assoc();
          // Verify the password using password_verify
          if (password_verify($password, $user['password'])) {
            // Start a session and store user information
            session_start();
            $_SESSION['attendee_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            send_login_notification($user['email'], $user['first_name']);

            // If "Remember Me" is checked, store a 30-day cookie tied to a DB token
            if (isset($_POST['rememberMe'])) {
              $token = bin2hex(random_bytes(32));
              $upd = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
              $upd->bind_param("si", $token, $user['id']);
              $upd->execute();
              $upd->close();
              setcookie('remember_me', $token, time() + 60 * 60 * 24 * 30, "/", "", false, true);
            }

            header("Location: ../index.php");
            exit();
          } else {
            $password_error = "Incorrect password";
          }
        } else {
          $email_error = "No account found with that email";
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
      }
    }
  ?>

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
        <li><a href="./login.php" class="nav-login-btn"><span>Login</span></a></li>
      </ul>
    </nav>
  </header>

  <main class="login-main">
    <div class="login-card">
      <h1>Login</h1>

      <div class="login-role">
        <a href="./login.php">Attendee</a>
        <span class="role-separator">|</span>
        <a href="./organizer-login.php">Organizer</a>
        <span class="role-separator">|</span>
        <a href="./admin-login.php">Admin</a>
      </div>

      <!-- LOGIN Form -->
      <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
        <!-- Hidden input to store the user role for login -->
        <input type ="hidden" id="userRole" name="userRole" value="attendee">

        <!-- Email field -->
        <div class="input-group">
          <input type="email" id="email" name="email" placeholder=" " value="<?php if (isset($email)) echo htmlspecialchars($email); ?>" required>
          <label for="email">Email Address</label>
          <span id="email_error"><?php echo htmlspecialchars($email_error); ?></span>
        </div>

        <!-- Password field -->
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder=" " value="<?php if (isset($password)) echo htmlspecialchars($password); ?>" required>
          <label for="password">Password</label>
          <!-- Show password toggle -->
          <span id="togglePassword" class="toggle-password" aria-label="Toggle password visibility">
            Show Password
          </span>
          <span id="password_error"><?php echo htmlspecialchars($password_error); ?></span>
        </div>

        <div class="remember-me">
          <input type="checkbox" id="rememberMe" name="rememberMe">
          <label for="rememberMe">Remember Me</label>
        </div>

        <button type="submit" class="btn">Login</button>
      </form>
      <a href="./signup.php" class="signup-link">New Attendee? Sign Up Here</a>
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

  <script src="../js/login.js"></script>
</body>

</html>
