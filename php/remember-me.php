<?php
// Restores the session from a "remember me" cookie if no session is active.
// Include AFTER session_start() and AFTER db_config.php.

if (
    !isset($_SESSION['attendee_id']) &&
    !isset($_SESSION['organizer_id']) &&
    !isset($_SESSION['admin_id']) &&
    isset($_COOKIE['remember_me'])
) {
    $token = $_COOKIE['remember_me'];

    $stmt = $conn->prepare("SELECT id, role FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['role'] === 'attendee') {
            $_SESSION['attendee_id'] = $user['id'];
        } elseif ($user['role'] === 'organiser') {
            $_SESSION['organizer_id'] = $user['id'];
        } elseif ($user['role'] === 'admin') {
            $_SESSION['admin_id'] = $user['id'];
        }

        $upd = $conn->prepare("UPDATE users SET last_visited = NOW() WHERE id = ?");
        $upd->bind_param("i", $user['id']);
        $upd->execute();
        $upd->close();
    }
    $stmt->close();
}
?>
