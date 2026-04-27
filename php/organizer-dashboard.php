<?php
  session_start();
  require_once '../db_config.php';
  require_once 'remember-me.php';

  if (!isset($_SESSION['organizer_id'])) {
    header("Location: organizer-login.php");
    exit();
  }

  $organizer_id = $_SESSION['organizer_id'];
  $error = "";

  if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'logout') {
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

    if ($action === 'create') {
      $date_time = $_POST['date_time'];
      $game_id   = (int) $_POST['game_id'];

      if (empty($date_time) || $game_id <= 0) {
        $error = "Please pick a date and a game";
      } else {
        $stmt = $conn->prepare("INSERT INTO events (date_time, game_id, organiser_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $date_time, $game_id, $organizer_id);
        $stmt->execute();
        $stmt->close();
      }
    }

    if ($action === 'update') {
      $event_id  = (int) $_POST['event_id'];
      $date_time = $_POST['date_time'];
      $game_id   = (int) $_POST['game_id'];

      $stmt = $conn->prepare("UPDATE events SET date_time = ?, game_id = ? WHERE id = ? AND organiser_id = ?");
      $stmt->bind_param("siii", $date_time, $game_id, $event_id, $organizer_id);
      $stmt->execute();
      $stmt->close();
    }

    if ($action === 'delete') {
      $ids = $_POST['ids'] ?? [];
      foreach ($ids as $id) {
        $event_id = (int) $id;
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organiser_id = ?");
        $stmt->bind_param("ii", $event_id, $organizer_id);
        $stmt->execute();
        $stmt->close();
      }
    }

    header("Location: organizer-dashboard.php");
    exit();
  }

  // Fetch this organizer's events
  $stmt = $conn->prepare("
    SELECT e.id, e.date_time, e.game_id, g.name AS game_name
    FROM events e
    JOIN games g ON e.game_id = g.id
    WHERE e.organiser_id = ?
    ORDER BY e.date_time
  ");
  $stmt->bind_param("i", $organizer_id);
  $stmt->execute();
  $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  // Fetch all games for the dropdown
  $games = $conn->query("SELECT id, name FROM games ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArenaSync | Organizer Dashboard</title>
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/organizer-dashboard.css">
</head>
<body>
  <div id="container">
  <header id="masthead">
    <a href="../index.php">
      <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
    </a>
    <p>ArenaSync</p>
    <nav class="navbar">
      <ul class="nav-links" id="nav-links">
        <li><a href="../index.php"><span>Home</span></a></li>
        <li><a href="./organizer-dashboard.php"><span>Dashboard</span></a></li>
        <li>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="nav-login-btn">Logout</button>
          </form>
        </li>
      </ul>
    </nav>
  </header>

  <main class="dashboard-main">
    <h1 class="section-title">Organizer Dashboard</h1>

    <?php if (!empty($error)): ?>
      <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <section class="create-section">
      <h2>Create New Event</h2>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <label>Date & Time
          <input type="datetime-local" name="date_time" required>
        </label>
        <label>Game
          <select name="game_id" required>
            <option value="">-- Select a game --</option>
            <?php foreach ($games as $game): ?>
              <option value="<?php echo (int) $game['id']; ?>"><?php echo htmlspecialchars($game['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button type="submit" class="btn">Add Event</button>
      </form>
    </section>

    <section class="events-section">
      <h2>Your Events</h2>

      <form method="POST" id="eventsForm">
        <input type="hidden" name="action" id="formAction" value="delete">

        <table class="events-table">
          <thead>
            <tr>
              <th></th>
              <th>Date &amp; Time</th>
              <th>Game</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($events) === 0): ?>
              <tr><td colspan="3">No events yet.</td></tr>
            <?php else: ?>
              <?php foreach ($events as $event): ?>
                <tr data-id="<?php echo (int) $event['id']; ?>">
                  <td><input type="checkbox" name="ids[]" value="<?php echo (int) $event['id']; ?>" class="row-check"></td>
                  <td class="cell-datetime" data-value="<?php echo htmlspecialchars($event['date_time']); ?>">
                    <?php echo htmlspecialchars($event['date_time']); ?>
                  </td>
                  <td class="cell-game" data-value="<?php echo (int) $event['game_id']; ?>">
                    <?php echo htmlspecialchars($event['game_name']); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <div class="action-buttons">
          <button type="button" id="editBtn" class="btn" disabled>Edit</button>
          <button type="button" id="deleteBtn" class="btn" disabled>Delete</button>
          <button type="button" id="saveBtn" class="btn" disabled>Save</button>
        </div>
      </form>
    </section>
  </main>

  <footer id="footer">
    <p>&copy; 2026 ArenaSync</p>
    <p class="footer-credits">Emmanuel &nbsp;&nbsp; Ahmad &nbsp;&nbsp; Miguel</p>
  </footer>
  </div>

  <script>
    // Game options for the inline editor
    const GAMES = <?php echo json_encode($games); ?>;
  </script>
  <script src="../js/organizer-dashboard.js"></script>
</body>
</html>
