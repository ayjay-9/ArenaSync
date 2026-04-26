<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * PROTECT PAGE
 */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ./admin-login.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/ArenaSync/db_config.php";
require_once __DIR__ . "/services/admin-user-services.php";

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
 * HANDLE CRUD ACTIONS
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

            <!-- LOGOUT -->
            <li>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout"
                        style="background:none;border:none;color:inherit;cursor:pointer;">
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

<!-- ADD USER -->
<div id="addModal" style="display:none;">
<form method="POST">

    <input type="hidden" name="action" value="create">

    <h3>Add User</h3>

    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="attendee">Attendee</option>
        <option value="organiser">Organiser</option>
    </select>

    <input name="first_name" placeholder="First Name">
    <input name="last_name" placeholder="Last Name">
    <input name="company" placeholder="Company">
    <input name="email" placeholder="Email" required>
    <input name="password" placeholder="Password" required>

    <button type="submit">Create</button>
    <button type="button" onclick="document.getElementById('addModal').style.display='none'">
        Cancel
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

    if (rows.length === 0) return;

    if (!confirm(`Delete ${rows.length} user(s)?`)) return;

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

    if (selected.length === 0) return;

    let container = document.getElementById('editModal');

    if (!container) {
        container = document.createElement('div');
        container.id = 'editModal';
        container.style = "position:fixed;top:10%;left:10%;right:10%;background:#fff;padding:20px;border:1px solid #ccc;";
        document.body.appendChild(container);
    }

    let html = `
        <form method="POST">
        <input type="hidden" name="action" value="update_batch">
        <h3>Edit Selected Users</h3>
    `;

    selected.forEach(cb => {
        const row = cb.closest('tr');

        html += `
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <input type="hidden" name="ids[]" value="${cb.value}">

                <select name="role[]">
                    <option value="admin" ${row.dataset.role === 'admin' ? 'selected' : ''}>Admin</option>
                    <option value="attendee" ${row.dataset.role === 'attendee' ? 'selected' : ''}>Attendee</option>
                    <option value="organiser" ${row.dataset.role === 'organiser' ? 'selected' : ''}>Organiser</option>
                </select>

                <input name="company[]" value="${row.dataset.company || ''}" placeholder="Company">
                <input name="email[]" value="${row.dataset.email}" placeholder="Email">
            </div>
        `;
    });

    html += `
        <button type="submit">Save Changes</button>
        <button type="button" onclick="location.reload()">Cancel</button>
        </form>
    `;

    container.innerHTML = html;
}

</script>

</body>
</html>