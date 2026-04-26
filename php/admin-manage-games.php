<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./login.php");
    exit();
}

require_once __DIR__ . "/../db_config.php";

$stmt = $conn->prepare("
    SELECT 
        id,
        name,
        category,
        description
    FROM games
    ORDER BY id DESC
");

$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArenaSync | Manage Games</title>

    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/home.css">
</head>

<body>

<div id="container">

    <!-- TOP NAV -->
    <header id="masthead">
        <a href="./admin-index.php">
            <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
        </a>

        <p>ArenaSync (Admin)</p>

        <nav>
            <ul class="nav-links" id="nav-links">
                <li><a href="./admin-index.php"><span>Home</span></a></li>
                <li><a href="./admin-dashboard.php"><span>Dashboard</span></a></li>
                <li><a href="./logout.php" class="nav-login-btn"><span>Logout</span></a></li>
            </ul>
        </nav>
    </header>

    <div id="main-content">

        <!-- SIDEBAR -->
        <aside id="sidebar">
            <ul>
                <li><a href="./admin-dashboard.php">Manage Users</a></li>
                <li><a href="./admin-manage-events.php">Manage Events</a></li>
                <li><a class="active" href="./admin-manage-games.php">Manage Games</a></li>
                <li><a href="./admin-statistics.php">View Statistics</a></li>
            </ul>
        </aside>

        <!-- MAIN -->
        <main id="main">

            <h2 class="section-title">Manage Games</h2>

            <!-- CRUD BUTTONS -->
            <div style="display:flex; justify-content:center; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                <button class="btn">Add Game</button>
                <button class="btn secondary" id="editBtn" disabled>Edit Game</button>
                <button class="btn danger" id="deleteBtn" disabled>Delete Game</button>
                <button class="btn success">Save</button>
            </div>

            <!-- TABLE -->
            <div style="overflow-x:auto;">

                <table style="width:100%; border-collapse: collapse; background: var(--bg-card); color: var(--text);">

                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Select</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($games)): ?>
                            <?php $i = 1; foreach ($games as $game): ?>

                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($game['name']) ?></td>
                                    <td><?= htmlspecialchars($game['category']) ?></td>
                                    <td><?= htmlspecialchars($game['description']) ?></td>
                                    <td>
                                        <input type="checkbox" class="row-check" value="<?= $game['id'] ?>">
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:20px;">
                                    No games found
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </main>

    </div>

    <footer id="footer">
        <p>&copy; 2026 ArenaSync Admin</p>
    </footer>

</div>

<script>
const checkboxes = document.querySelectorAll('.row-check');
const editBtn = document.getElementById('editBtn');
const deleteBtn = document.getElementById('deleteBtn');

function updateButtons() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    editBtn.disabled = checked === 0;
    deleteBtn.disabled = checked === 0;
}

checkboxes.forEach(cb => cb.addEventListener('change', updateButtons));
</script>

<script src="../js/home.js"></script>

</body>
</html>