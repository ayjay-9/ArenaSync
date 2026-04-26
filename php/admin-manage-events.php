<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ./login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../db_config.php";
require_once __DIR__ . "/services/admin-events-services.php";

/**
 * HANDLE ACTIONS
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
 * LOAD EVENTS
 */
$stmt = $conn->prepare("
    SELECT 
        events.id,
        events.date_time,
        games.name AS game_name,
        games.category AS game_category,
        users.company,
        users.first_name,
        users.last_name,
        (
            SELECT COUNT(*) 
            FROM bookings 
            WHERE bookings.event_id = events.id
        ) AS bookings_count
    FROM events
    JOIN games ON events.game_id = games.id
    JOIN users ON events.organiser_id = users.id
    ORDER BY events.date_time DESC
");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * LOAD DROPDOWNS
 */
$games = $conn->query("SELECT id, name FROM games")->fetchAll(PDO::FETCH_ASSOC);

$organisers = $conn->query("
    SELECT id, company, first_name, last_name 
    FROM users 
    WHERE role = 'organiser'
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ArenaSync | Manage Events</title>

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
            <li><a href="./logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div id="main-content">

<aside id="sidebar">
    <ul>
        <li><a href="./admin-dashboard.php">Manage Users</a></li>
        <li><a class="active">Manage Events</a></li>
        <li><a href="./admin-manage-games.php">Manage Games</a></li>
        <li><a href="./admin-statistics.php">View Statistics</a></li>
    </ul>
</aside>

<main id="main">

<h2>Manage Events</h2>

<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">

    <button onclick="document.getElementById('addModal').style.display='block'">
        Add Event
    </button>

    <button id="editBtn" disabled onclick="openBatchEdit()">
        Edit Event(s)
    </button>

    <button id="deleteBtn" disabled onclick="submitDelete()">
        Delete Event(s)
    </button>

</div>

<table border="1" width="100%">
<thead>
<tr>
    <th>S/N</th>
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

<!-- ADD EVENT -->
<div id="addModal" style="display:none;">
<form method="POST">

    <input type="hidden" name="action" value="create">

    <h3>Add Event</h3>

    <label>Game</label>
    <select name="game_id" required>
        <option value="">Select Game</option>
        <?php foreach ($games as $g): ?>
            <option value="<?= $g['id'] ?>">
                <?= htmlspecialchars($g['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Organiser</label>
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

    <button type="submit">Create</button>
    <button type="button" onclick="document.getElementById('addModal').style.display='none'">
        Cancel
    </button>

</form>
</div>

<!-- EDIT MODAL -->
<div id="editModal" style="display:none;">
<form method="POST">

    <input type="hidden" name="action" value="update_batch">

    <h3>Edit Events</h3>

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

        container.innerHTML += `
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <input type="hidden" name="ids[]" value="${cb.value}">
                <input name="date_time[]" value="${row.dataset.datetime}" type="datetime-local">
            </div>
        `;
    });

    document.getElementById('editModal').style.display = 'block';
}

</script>

</body>
</html>