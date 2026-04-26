<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./admin-login.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/ArenaSync/db_config.php";
require_once __DIR__ . "/services/admin-user-services.php";

/**
 * LOGOUT HANDLER (UNIFIED SYSTEM)
 */
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

/**
 * HANDLE ACTIONS
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {

    $action = $_POST['action'] ?? '';

    switch ($action) {

        case 'create':
            createUser($conn, $_POST);
            break;

        case 'update_batch':
            updateUsersBatch($conn, $_POST);
            break;

        case 'delete':
            deleteUsers($conn, $_POST['ids'] ?? []);
            break;
    }

    header("Location: admin-dashboard.php");
    exit();
}

/**
 * LOAD USERS
 */
$stmt = $conn->prepare("
    SELECT id, role, first_name, last_name, company, email, created_at, last_visited
    FROM users
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <img src="../images/home-page-icon.png" class="home-page-icon">
    </a>

    <p>ArenaSync (Admin)</p>

    <nav>
        <ul class="nav-links">
            <li><a href="./admin-index.php">Home</a></li>
            <li><a href="./admin-dashboard.php">Dashboard</a></li>

            <!-- FIXED LOGOUT -->
            <li>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout" style="background:none;border:none;color:inherit;cursor:pointer;">
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</header>

<div id="main-content">

<aside id="sidebar">
    <ul>
        <li><a class="active">Manage Users</a></li>
        <li><a href="./admin-manage-events.php">Manage Events</a></li>
        <li><a href="./admin-manage-games.php">Manage Games</a></li>
        <li><a href="./admin-statistics.php">View Statistics</a></li>
    </ul>
</aside>

<main id="main">

<h2>Manage Users</h2>

<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    <button onclick="document.getElementById('addModal').style.display='block'">
        Add User
    </button>

    <button id="editBtn" disabled onclick="openBatchEdit()">
        Edit User(s)
    </button>

    <button id="deleteBtn" disabled onclick="submitDelete()">
        Delete User(s)
    </button>
</div>

<table border="1" width="100%">
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

<?php $i = 1; foreach ($users as $u): ?>

<?php
$name = $u['company'] ?: trim($u['first_name'] . ' ' . $u['last_name']);
?>

<tr
    data-id="<?= $u['id'] ?>"
    data-role="<?= $u['role'] ?>"
    data-first="<?= htmlspecialchars($u['first_name']) ?>"
    data-last="<?= htmlspecialchars($u['last_name']) ?>"
    data-company="<?= htmlspecialchars($u['company']) ?>"
    data-email="<?= htmlspecialchars($u['email']) ?>"
>

<td><?= $i++ ?></td>
<td><?= htmlspecialchars($u['role']) ?></td>
<td><?= htmlspecialchars($name ?: 'N/A') ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= htmlspecialchars($u['created_at']) ?></td>
<td><?= htmlspecialchars($u['last_visited'] ?? 'Never') ?></td>

<td>
    <input type="checkbox" class="row" value="<?= $u['id'] ?>">
</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</main>
</div>

</body>
</html>