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

    $check = $conn->prepare("SELECT event_id FROM bookings WHERE user_id = ? AND event_id = ?");
    $check->bind_param("ii", $attendee_id, $event_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        echo json_encode(['error' => 'Already registered for this event']);
        exit();
    }
    $check->close();

    $ins = $conn->prepare("INSERT INTO bookings (user_id, event_id) VALUES (?, ?)");
    $ins->bind_param("ii", $attendee_id, $event_id);
    if ($ins->execute()) {
        $ins->close();
        echo json_encode(['success' => true]);
    } else {
        $ins->close();
        echo json_encode(['error' => 'Failed to save booking']);
    }
