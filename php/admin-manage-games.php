<?php
session_start();

/**
 * PROTECT PAGE (admin must be logged in)
 */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ./admin-login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . "/ArenaSync/db_config.php";
require_once __DIR__ . "/services/admin-games-services.php";

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
 * HANDLE ACTIONS (IGNORE LOGOUT POSTS)
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
$stmt = $conn->prepare("
    SELECT id, name, category, description
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

<header id="masthead">
    <a href="./admin-index.php">
        <img src="../images/home-page-icon.png" class="home-page-icon">
    </a>

    <p>ArenaSync (Admin)</p>

    <nav>
        <ul class="nav-links">
            <li><a href="./admin-index.php">Home</a></li>
            <li><a href="./admin-dashboard.php">Dashboard</a></li>

            <!-- FIXED LOGOUT (same as working users page) -->
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
        <li><a href="./admin-dashboard.php">Manage Users</a></li>
        <li><a href="./admin-manage-events.php">Manage Events</a></li>
        <li><a class="active">Manage Games</a></li>
        <li><a href="./admin-statistics.php">View Statistics</a></li>
    </ul>
</aside>

<main id="main">

<h2>Manage Games</h2>

<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">

    <button onclick="document.getElementById('addModal').style.display='block'">
        Add Game
    </button>

    <button id="editBtn" disabled onclick="openBatchEdit()">
        Edit Game(s)
    </button>

    <button id="deleteBtn" disabled onclick="submitDelete()">
        Delete Game(s)
    </button>

</div>

<table border="1" width="100%">
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

<!-- ADD GAME -->
<div id="addModal" style="display:none;">
<form method="POST">

    <input type="hidden" name="action" value="create">

    <h3>Add Game</h3>

    <input name="name" placeholder="Game Name" required>
    <input name="category" placeholder="Category" required>
    <textarea name="description" placeholder="Description"></textarea>

    <button type="submit">Create</button>
    <button type="button" onclick="this.closest('#addModal').style.display='none'">Cancel</button>

</form>
</div>

<!-- EDIT BATCH -->
<div id="editModal" style="display:none;">
<form method="POST">

    <input type="hidden" name="action" value="update_batch">

    <h3>Edit Selected Games</h3>

    <div id="batchEditContainer"></div>

    <button type="submit">Save Changes</button>
    <button type="button" onclick="document.getElementById('editModal').style.display='none'">
        Close
    </button>

</form>
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
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <input type="hidden" name="ids[]" value="${cb.value}">

                <input name="name[]" value="${row.dataset.name}" placeholder="Name">
                <input name="category[]" value="${row.dataset.category}" placeholder="Category">
                <textarea name="description[]">${row.dataset.description}</textarea>
            </div>
        `;
    });

    document.getElementById('editModal').style.display = 'block';
}

</script>

</body>
</html>