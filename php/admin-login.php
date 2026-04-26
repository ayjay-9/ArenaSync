<?php
session_start();
require_once('../db_config.php');

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST['email'] ?? '';
    $userPassword = $_POST['password'] ?? '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $db_user,
            $db_pass
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get admin only
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($userPassword, $user['password'])) {

            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['first_name'];

            header("Location: admin-index.php");
            exit();

        } else {
            $error = "Invalid admin credentials.";
        }

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArenaSync | Admin Login</title>
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/login.css">
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
        <li><a href="../index.php"><span>HOME</span></a></li>
        <li><a href="./login.php" class="nav-login-btn"><span>LOGIN</span></a></li>
      </ul>
    </nav>
  </header>

  <main class="login-main">
    <div class="login-card">
      <h1>Admin Login</h1>

      <div class="login-role">
        <a href="./login.php">Attendee</a>
        <span class="role-separator">|</span>
        <a href="./organizer-login.php">Organizer</a>
        <span class="role-separator">|</span>
        <a href="./admin-login.php">Admin</a>
      </div>

      <?php if ($error): ?>
        <p style="color:red; text-align:center;">
          <?php echo $error; ?>
        </p>
      <?php endif; ?>

      <form id="loginForm" method="POST" action="">
        
        <!-- Hidden role (kept for consistency, optional) -->
        <input type="hidden" name="userRole" value="admin">

        <!-- Email -->
        <div class="input-group">
          <input type="email" id="email" name="email" placeholder=" " required>
          <label for="email">Email Address</label>
        </div>

        <!-- Password -->
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder=" " required>
          <label for="password">Password</label>

          <span id="togglePassword" class="toggle-password">
            Show Password
          </span>
        </div>

        <div class="remember-me">
          <input type="checkbox" id="rememberMe" name="rememberMe">
          <label for="rememberMe">Remember Me</label>
        </div>

        <button type="submit" class="btn">Login</button>
      </form>

    </div>
  </main>

  <footer id="footer">
    <div class="footer-socials">
      <a href="#"><img src="../icons/facebook-app-symbol.png" class="footer-icon"></a>
      <a href="#"><img src="../icons/twitter-black-shape.png" class="footer-icon"></a>
      <a href="#"><img src="../icons/twitch.png" class="footer-icon"></a>
      <a href="#"><img src="../icons/youtube.png" class="footer-icon"></a>
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