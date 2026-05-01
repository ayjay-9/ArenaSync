<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./admin-login.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/ArenaSync/db_config.php";
require_once __DIR__ . "/services/admin-user-services.php";

/* LOGOUT */
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

/* ACTIONS */
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

/* USERS */
$result = $conn->query("
    SELECT id, role, first_name, last_name, company, email, created_at, last_visited
    FROM users
");

if (!$result) {
    die("Query failed: " . $conn->error);
}

$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ArenaSync | Admin Dashboard</title>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/home.css">
<link rel="stylesheet" href="../css/admin.css">
</head>

<body>

<div id="container">

<header id="masthead">

    <a href="./admin-index.php">
        <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
    </a>

    <p>ArenaSync (Admin)</p>

    <nav class="navbar">

        <div class="nav-left">

            <ul class="nav-links">

                <li class="nav-item-with-theme">

                    <div class="theme-toggle inline-theme">
                        <div class="theme-slider">
                            <div class="theme-knob"></div>

                            <button data-theme="light">Light</button>
                            <button data-theme="dark">Dark</button>
                            <button data-theme="negative">Blood</button>
                        </div>
                    </div>

                    <a href="./admin-index.php"><span>Home</span></a>
                </li>

                <li><a href="./admin-dashboard.php"><span>Dashboard</span></a></li>

            </ul>

        </div>

        <div class="nav-right">

            <div class="hamburger" id="hamburger">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>

            <ul class="nav-links">
                <li>
                    <form method="POST">
                        <button type="submit" name="logout" class="nav-login-btn">
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>

        </div>

    </nav>

</header>

<div id="main-content">

<aside id="sidebar">
    <div class="sidebar-header">
        <h3>Admin Panel</h3>
    </div>

    <ul class="sidebar-menu">
        <li><a class="active">Manage Users</a></li>
        <li><a href="./admin-manage-events.php">Manage Events</a></li>
        <li><a href="./admin-manage-games.php">Manage Games</a></li>
        <li><a href="./admin-statistics.php">View Statistics</a></li>
    </ul>
</aside>

<main id="main">

<h2 class="section-title">Manage Users</h2>

<div class="admin-actions">
    <button class="btn" onclick="openAddModal()">Add User</button>
    <button class="btn secondary" id="editBtn" disabled onclick="openBatchEdit()">Edit</button>
    <button class="btn danger" id="deleteBtn" disabled onclick="submitDelete()">Delete</button>
</div>

<table class="admin-table">
<thead>
<tr>
    <th>#</th>
    <th>Role</th>
    <th>Name / Company</th>
    <th>Email</th>
    <th>Created</th>
    <th>Last Visit</th>
    <th>Select</th>
</tr>
</thead>

<tbody>
<?php $i = 1; foreach ($users as $u): ?>
<?php $name = $u['company'] ?: trim($u['first_name'] . ' ' . $u['last_name']); ?>

<tr
    data-id="<?= $u['id'] ?>"
    data-role="<?= htmlspecialchars($u['role']) ?>"
    data-company="<?= htmlspecialchars($u['company'] ?? '') ?>"
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

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content large">
        <form method="POST">
            <input type="hidden" name="action" value="create">

            <h3>Add User</h3>

            <!-- controlled role selection -->
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="admin">Admin</option>
                <option value="attendee">Attendee</option>
                <option value="organiser">Organiser</option>
            </select>

            <input name="first_name" placeholder="First Name">
            <input name="last_name" placeholder="Last Name">
            <input name="company" placeholder="Company">
            <input name="email" placeholder="Email" required>
            <input name="password" type="password" placeholder="Password" required>

            <button type="submit" class="btn">Create User</button>
            <button type="button" class="btn secondary" onclick="closeAddModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content large">

        <form method="POST">
            <input type="hidden" name="action" value="update_batch">

            <h3>Edit Users</h3>

            <div id="batchEditContainer"></div>

            <button type="submit" class="btn">Save Changes</button>
            <button type="button" class="btn secondary" onclick="closeEditModal()">Cancel</button>

        </form>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const checkboxes = document.querySelectorAll(".row");
    const editBtn = document.getElementById("editBtn");
    const deleteBtn = document.getElementById("deleteBtn");

    function selectedIds() {
        return [...checkboxes]
            .filter(c => c.checked)
            .map(c => c.value);
    }

    function updateButtons() {
        const has = selectedIds().length > 0;
        editBtn.disabled = !has;
        deleteBtn.disabled = !has;
    }

    checkboxes.forEach(c => c.addEventListener("change", updateButtons));
    updateButtons();

    window.submitDelete = function () {
        const ids = selectedIds();
        if (!ids.length) return;

        if (!confirm("Delete selected users?")) return;

        const form = document.createElement("form");
        form.method = "POST";

        form.innerHTML = `<input name="action" value="delete">`;

        ids.forEach(id => {
            const input = document.createElement("input");
            input.name = "ids[]";
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    };

    window.openAddModal = () =>
        document.getElementById("addModal").classList.add("show");

    window.closeAddModal = () =>
        document.getElementById("addModal").classList.remove("show");

    window.openBatchEdit = () => {
        const container = document.getElementById("batchEditContainer");
        container.innerHTML = "";

        selectedIds().forEach(id => {
            const row = document.querySelector(`tr[data-id="${id}"]`);

            container.innerHTML += `
                <input type="hidden" name="ids[]" value="${id}">
                <input name="role[]" value="${row.dataset.role}">
                <input name="company[]" value="${row.dataset.company}">
                <input name="email[]" value="${row.dataset.email}">
                <input name="password[]" placeholder="New password (optional)">
                <hr>
            `;
        });

        document.getElementById("editModal").classList.add("show");
    };

    window.closeEditModal = () =>
        document.getElementById("editModal").classList.remove("show");

});
</script>

<script src="../js/admin-cookies.js"></script>

</body>
</html>