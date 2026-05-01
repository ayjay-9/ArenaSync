<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./admin-login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../db_config.php";
require_once __DIR__ . "/services/admin-events-services.php";

/**
 * LOGOUT HANDLER
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
            createEvent($conn, $_POST);
            break;

        case 'update_batch':
            updateEventsBatch($conn, $_POST);
            break;

        case 'delete':
            deleteEvents($conn, $_POST['ids'] ?? []);
            break;
    }

    header("Location: admin-manage-events.php");
    exit();
}

/**
 * LOAD DATA 
 */
$events = getAllEvents($conn);
$games = getGames($conn);
$organisers = getOrganisers($conn);
?>

<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ArenaSync | Manage Events</title>

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
        <li><a class="active">Manage Events</a></li>
        <li><a href="./admin-manage-games.php">Manage Games</a></li>
        <li><a href="./admin-statistics.php">View Statistics</a></li>
    </ul>
</aside>

<main id="main">

<h2 class="section-title">Manage Events</h2>

<div class="admin-actions">
    <button class="btn" onclick="openAddModal()">Add Event</button>
    <button class="btn secondary" id="editBtn" disabled onclick="openBatchEdit()">Edit</button>
    <button class="btn danger" id="deleteBtn" disabled onclick="submitDelete()">Delete</button>
</div>

<table class="admin-table">
<thead>
<tr>
    <th>#</th>
    <th>Game</th>
    <th>Category</th>
    <th>Organizer</th>
    <th>Date & Time</th>
    <th>Bookings</th>
    <th>Select</th>
</tr>
</thead>

<tbody>

<?php $i = 1; foreach ($events as $e): ?>

<?php
$organizer = $e['company']
    ?: trim($e['first_name'] . ' ' . $e['last_name']);

if ($organizer === '') $organizer = 'N/A';
?>

<tr
    data-id="<?= $e['id'] ?>"
    data-game="<?= htmlspecialchars($e['game_name']) ?>"
    data-category="<?= htmlspecialchars($e['game_category']) ?>"
    data-datetime="<?= htmlspecialchars($e['date_time']) ?>"
>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($e['game_name']) ?></td>
<td><?= htmlspecialchars($e['game_category']) ?></td>
<td><?= htmlspecialchars($organizer) ?></td>
<td><?= htmlspecialchars($e['date_time']) ?></td>
<td><?= htmlspecialchars($e['bookings_count']) ?></td>

<td>
    <input type="checkbox" class="row" value="<?= $e['id'] ?>">
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

            <h3>Add Event</h3>

            <select name="game_id" required>
                <option value="">Select Game</option>
                <?php foreach ($games as $g): ?>
                    <option value="<?= $g['id'] ?>">
                        <?= htmlspecialchars($g['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="organiser_id" required>
                <option value="">Select Organiser</option>
                <?php foreach ($organisers as $o): ?>
                    <?php
                        $name = $o['company']
                            ?: trim($o['first_name'] . ' ' . $o['last_name']);
                    ?>
                    <option value="<?= $o['id'] ?>">
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input name="date_time" type="datetime-local" required>

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

            <h3>Edit Selected Events</h3>

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
    if (rows.length === 0) return;

    if (!confirm(`Delete ${rows.length} event(s)?`)) return;

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

        const game = row.dataset.game;
        const category = row.dataset.category;
        const datetime = row.dataset.datetime;

        container.innerHTML += `
            <div style="border:1px solid var(--border); padding:15px; margin-bottom:12px; border-radius:8px; background:var(--bg-card);">

                <div style="margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid var(--border);">

                    <strong style="color:var(--accent); font-size:0.95rem;">
                        ${game}
                    </strong>

                    <div style="font-size:0.8rem; color:var(--text-muted); margin-top:3px;">
                        Event ID: ${cb.value} | Category: ${category}
                    </div>

                </div>

                <input type="hidden" name="ids[]" value="${cb.value}">

                <label style="font-size:0.8rem; color:var(--text-muted);">Date & Time</label>
                <input name="date_time[]" value="${datetime}" type="datetime-local">

            </div>
        `;
    });

    document.getElementById('editModal').classList.add('show');
}

</script>

<script src="../js/admin-cookies.js"></script>

</body>
</html>