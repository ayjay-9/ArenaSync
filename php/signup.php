<?php
    // Access the database
    require_once '../db_config.php';

    // Set error message variables for the signup form
    $email_error     = "";
    $password_error  = "";
    $firstName_error = "";
    $lastName_error  = "";
    $success_message = "";

    // Define minimal allowed personal email domains using a regex pattern (gmail, yahoo, outlook, icloud, and duck)
    $allowed_domains = 'gmail\.com|yahoo\.com|outlook\.com|icloud\.com|duck\.com';

    if ($_SERVER['REQUEST_METHOD'] === "POST") {
      $role      = $_POST['userRole'];
      $firstName = ucwords(trim($_POST["firstName"])); // Convert first name to title case and trim whitespace
      $lastName  = ucwords(trim($_POST["lastName"])); // Convert last name to title case and trim whitespace
      $email     = trim($_POST["email"]);
      $password  = $_POST["password"];

      // Validate first name and last name (only letters, spaces and hyphens allowed)
      if (!preg_match("/^[a-zA-Z\s-]+$/", $firstName)) {
        $firstName_error = "First name can only contain letters, spaces and hyphens";
      }
      if (!preg_match("/^[a-zA-Z\s-]+$/", $lastName)) {
        $lastName_error = "Last name can only contain letters, spaces and hyphens";
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format";
      } elseif (!preg_match("/($allowed_domains)$/", $email)) {
        $email_error = "Please use a personal email address";
      }

      if (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long";
      }

      // If there are no validation errors, proceed with registration
      if (empty($email_error) && empty($password_error) && empty($firstName_error) && empty($lastName_error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (role, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $role, $firstName, $lastName, $email, $hashed_password);

        if ($stmt->execute()) {
          $success_message = "Account created successfully! Redirecting to login...";
        } else {
          // Error code 1062 = duplicate entry (email already registered)
          if ($conn->errno === 1062) {
            $email_error = "An account with this email already exists";
          } else {
            $email_error = "Registration failed, please try again";
          }
        }

        $stmt->close();
        $conn->close();
      }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArenaSync | Signup</title>
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/login.css">
  <!-- Redirect to login after successful signup -->
  <?php if (!empty($success_message)): ?>
    <meta http-equiv="refresh" content="2;url=login.php">
  <?php endif; ?>
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
      <ul class="nav-links" id="nav-links">
        <li><a href="../index.php"><span>Home</span></a></li>
        <li><a href="./login.php" class="nav-login-btn"><span>Login</span></a></li>
      </ul>
    </nav>
  </header>

  <main class="login-main">
    <div class="login-card">
      <?php if (!empty($success_message)): ?>
        <p id="signup-success" class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
      <?php endif; ?>
      <h1>Sign Up</h1>

      <div class="login-role">
        <a href="./signup.php">Attendee</a>
        <span class="role-separator">|</span>
        <a href="./organizer-signup.php">Organizer</a>
      </div>
      <!-- Signup form -->
      <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
        <!-- Hidden input to store the user role for signup -->
        <input type ="hidden" id="userRole" name="userRole" value="attendee">

        <!-- Name fields -->
        <div class="input-group">
          <input type="text" id="firstName" name="firstName" placeholder=" " value="<?php if (isset($firstName)) echo htmlspecialchars($firstName); ?>" required>
          <label for="firstName">First Name</label>
          <span id="firstName_error"><?php echo htmlspecialchars($firstName_error); ?></span>
        </div>
        <div class="input-group">
          <input type="text" id="lastName" name="lastName" placeholder=" " value="<?php if (isset($lastName)) echo htmlspecialchars($lastName); ?>" required>
          <label for="lastName">Last Name</label>
          <span id="lastName_error"><?php echo htmlspecialchars($lastName_error); ?></span>
        </div>

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

        <button type="submit" class="btn">Sign Up</button>
      </form>
      <a href="./login.php" class="signup-link">Already have an account? Login Here</a>
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
