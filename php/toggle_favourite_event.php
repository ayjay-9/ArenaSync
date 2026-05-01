<?php
    session_start();
    require_once '../db_config.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['attendee_id'])) {
        echo json_encode(['error' => 'Not authenticated']);
        exit();
    }

    $attendee_id = (int) $_SESSION['attendee_id'];
    $event_id    = (int) ($_POST['event_id'] ?? 0);

    if ($event_id <= 0) {
        echo json_encode(['error' => 'Invalid event']);
        exit();
    }

    $check = $conn->prepare("SELECT id FROM favourite_events WHERE attendee_id = ? AND event_id = ?");
    $check->bind_param("ii", $attendee_id, $event_id);
    $check->execute();
    $check->store_result();
    $already_fav = $check->num_rows > 0;
    $check->close();

    if ($already_fav) {
        $del = $conn->prepare("DELETE FROM favourite_events WHERE attendee_id = ? AND event_id = ?");
        $del->bind_param("ii", $attendee_id, $event_id);
        $del->execute();
        $del->close();
        echo json_encode(['favourited' => false]);
    } else {
        $ins = $conn->prepare("INSERT INTO favourite_events (attendee_id, event_id) VALUES (?, ?)");
        $ins->bind_param("ii", $attendee_id, $event_id);
        $ins->execute();
        $ins->close();
        echo json_encode(['favourited' => true]);
    }
