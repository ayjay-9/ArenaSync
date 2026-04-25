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
        <!-- <li><a href="./events.php"><span>EVENTS</span></a></li>
        <li><a href="./support.php"><span>SUPPORT</span></a></li> -->
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

      <form id="loginForm">
        <!-- Hidden input to store the admin role for login -->
        <input type ="hidden" id="userRole" name="userRole" value="admin">

        <!-- Email field -->
        <div class="input-group">
          <input type="email" id="email" name="email" placeholder=" " required>
          <label for="email">Email Address</label>
        </div>

        <!-- Password field -->
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder=" " required>
          <label for="password">Password</label>
          <!-- Show password toggle -->
          <span id="togglePassword" class="toggle-password" aria-label="Toggle password visibility">
            Show Password
          </span>
        </div>

        <div class="remember-me">
          <input type="checkbox" id="rememberMe" name="rememberMe">
          <label for="rememberMe">Remember Me</label>
        </div>

        <button type="submit" class="btn">Welcome, Admin!</button>
      </form>
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
