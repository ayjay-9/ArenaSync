<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./login.php");
    exit();
}

require_once __DIR__ . "/../db_config.php";

try {
    $stmt = $conn->prepare("
        SELECT id, role, first_name, last_name, company, email, created_at, last_visited
        FROM users
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArenaSync | Admin Dashboard</title>

    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/home.css">
</head>

<body>

<div id="container">

    <header id="masthead">
        <a href="./admin-index.php">
            <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
        </a>

        <p>ArenaSync (Admin)</p>

        <nav>
            <ul class="nav-links" id="nav-links">
                <li><a href="./admin-index.php"><span>Home</span></a></li>
                <li><a href="./admin-dashboard.php"><span>Dashboard</span></a></li>
                <li><a href="./logout.php"><span>Logout</span></a></li>
            </ul>
        </nav>
    </header>

    <div id="main-content">

        <aside id="sidebar">
            <ul>
                <li><a class="active" href="./admin-dashboard.php">Manage Users</a></li>
                <li><a href="./admin-manage-events.php">Manage Events</a></li>
                <li><a href="./admin-manage-games.php">Manage Games</a></li>
                <li><a href="./admin-statistics.php">View Statistics</a></li>
            </ul>
        </aside>

        <main id="main">

            <h2 class="section-title">Manage Users</h2>

            <div style="display:flex; justify-content:center; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                <button class="btn">Add User</button>
                <button class="btn secondary" id="editBtn" disabled>Edit User</button>
                <button class="btn danger" id="deleteBtn" disabled>Delete User</button>
                <button class="btn success">Save</button>
            </div>

            <div style="overflow-x:auto;">

                <table style="width:100%; border-collapse: collapse;">

                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Role</th>
                            <th>Name / Company</th>
                            <th>Email</th>
                            <th>Created At</th>
                            <th>Last Visited</th>
                            <th>Select</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($users)): ?>
                        <?php $i = 1; foreach ($users as $user): ?>

                            <?php
                                $displayName = $user['company'];

                                if (!$displayName) {
                                    $displayName = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
                                }

                                if ($displayName === '') {
                                    $displayName = 'N/A';
                                }
                            ?>

                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= htmlspecialchars($displayName) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['created_at']) ?></td>
                                <td><?= htmlspecialchars($user['last_visited'] ?? 'Never') ?></td>
                                <td>
                                    <input type="checkbox" class="row-check" value="<?= $user['id'] ?>">
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:20px;">
                                No users found
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