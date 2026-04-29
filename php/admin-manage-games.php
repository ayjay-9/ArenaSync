<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./admin-login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . "/ArenaSync/db_config.php";
require_once __DIR__ . "/services/admin-games-services.php";

/**
 * LOGOUT
 */
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

/**
 * ACTIONS
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            createGame($conn, $_POST);
            break;

        case 'update_batch':
            updateGamesBatch($conn, $_POST);
            break;

        case 'delete':
            deleteGames($conn, $_POST['ids'] ?? []);
            break;
    }

    header("Location: admin-manage-games.php");
    exit();
}

/**
 * LOAD GAMES
 */
$result = $conn->query("
    SELECT id, name, category, description
    FROM games
    ORDER BY id DESC
");

$games = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ArenaSync | Manage Games</title>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/home.css">
<link rel="stylesheet" href="../css/admin.css">
</head>

<body>

<div id="container">

<header id="masthead">

    <div class="masthead-left">
        <a href="./admin-index.php">
            <img src="../images/home-page-icon.png" class="home-page-icon" alt="ArenaSync Logo">
        </a>

        <p>ArenaSync (Admin)</p>
    </div>

    <nav class="navbar">

        <ul class="nav-links" id="nav-links">

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

            <li>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout" class="nav-login-btn">
                        <span>Logout</span>
                    </button>
                </form>
            </li>

        </ul>

    </nav>

</header>

<div id="main-content">

<aside id="sidebar">

    <div class="sidebar-header">
        <h3>Admin Panel</h3>
    </div>

    <ul class="sidebar-menu">
        <li><a href="./admin-dashboard.php">Manage Users</a></li>
        <li><a href="./admin-manage-events.php">Manage Events</a></li>
        <li><a class="active" href="./admin-manage-games.php">Manage Games</a></li>
        <li><a href="./admin-statistics.php">View Statistics</a></li>
    </ul>

</aside>

<main id="main">

<h2 class="section-title">Manage Games</h2>

<div class="admin-actions">
    <button class="btn" onclick="openAddModal()">Add Game</button>
    <button class="btn secondary" id="editBtn" disabled onclick="openBatchEdit()">Edit</button>
    <button class="btn danger" id="deleteBtn" disabled onclick="submitDelete()">Delete</button>
</div>

<table class="admin-table">
<thead>
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Category</th>
    <th>Description</th>
    <th>Select</th>
</tr>
</thead>

<tbody>

<?php $i = 1; foreach ($games as $g): ?>

<tr
    data-id="<?= $g['id'] ?>"
    data-name="<?= htmlspecialchars($g['name']) ?>"
    data-category="<?= htmlspecialchars($g['category']) ?>"
    data-description="<?= htmlspecialchars($g['description']) ?>"
>

<td><?= $i++ ?></td>
<td><?= htmlspecialchars($g['name']) ?></td>
<td><?= htmlspecialchars($g['category']) ?></td>
<td><?= htmlspecialchars($g['description']) ?></td>

<td>
    <input type="checkbox" class="row" value="<?= $g['id'] ?>">
</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</main>
</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <form method="POST">

            <input type="hidden" name="action" value="create">

            <h3>Add Game</h3>

            <input name="name" placeholder="Game Name" required>
            <input name="category" placeholder="Category" required>
            <textarea name="description" placeholder="Description"></textarea>

            <div class="modal-actions">
                <button type="submit" class="btn">Create</button>
                <button type="button" class="btn secondary" onclick="closeAddModal()">Cancel</button>
            </div>

        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content large">

        <form method="POST">
            <input type="hidden" name="action" value="update_batch">

            <h3>Edit Selected Games</h3>

            <div id="batchEditContainer"></div>

            <div class="modal-actions">
                <button type="submit" class="btn">Save Changes</button>
                <button type="button" class="btn secondary" onclick="closeEditModal()">Cancel</button>
            </div>

        </form>

    </div>
</div>

<script>

const checkboxes = document.querySelectorAll('.row');
const editBtn = document.getElementById('editBtn');
const deleteBtn = document.getElementById('deleteBtn');

function updateButtons() {
    const checked = document.querySelectorAll('.row:checked').length;
    editBtn.disabled = checked === 0;
    deleteBtn.disabled = checked === 0;
}

checkboxes.forEach(cb => cb.addEventListener('change', updateButtons));

function openAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

function submitDelete() {

    const rows = [...document.querySelectorAll('.row:checked')];

    if (!confirm(`Delete ${rows.length} game(s)?`)) return;

    const ids = rows.map(c => c.value);

    const form = document.createElement('form');
    form.method = 'POST';

    form.innerHTML = `
        <input name="action" value="delete">
        <input name="ids" value='${JSON.stringify(ids)}'>
    `;

    document.body.appendChild(form);
    form.submit();
}

function openBatchEdit() {

    const selected = [...document.querySelectorAll('.row:checked')];
    const container = document.getElementById('batchEditContainer');

    container.innerHTML = '';

    selected.forEach(cb => {
        const row = cb.closest('tr');

        container.innerHTML += `
            <div style="border:1px solid var(--border); padding:10px; margin-bottom:10px;">
                <input type="hidden" name="ids[]" value="${cb.value}">

                <input name="name[]" value="${row.dataset.name}" placeholder="Name">
                <input name="category[]" value="${row.dataset.category}" placeholder="Category">
                <textarea name="description[]" placeholder="Description">${row.dataset.description}</textarea>
            </div>
        `;
    });

    document.getElementById('editModal').classList.add('show');
}

</script>

<script src="../js/admin-cookies.js"></script>

</body>
</html>