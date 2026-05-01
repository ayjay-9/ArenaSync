<?php
    session_start();
    require_once '../db_config.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['attendee_id'])) {
        echo json_encode(['error' => 'Not authenticated']);
        exit();
    }

    $attendee_id  = (int) $_SESSION['attendee_id'];
    $organizer_id = (int) ($_POST['organizer_id'] ?? 0);

    if ($organizer_id <= 0) {
        echo json_encode(['error' => 'Invalid organizer']);
        exit();
    }

    $check = $conn->prepare("SELECT id FROM favourite_organizers WHERE attendee_id = ? AND organizer_id = ?");
    $check->bind_param("ii", $attendee_id, $organizer_id);
    $check->execute();
    $check->store_result();
    $already_fav = $check->num_rows > 0;
    $check->close();

    if ($already_fav) {
        $del = $conn->prepare("DELETE FROM favourite_organizers WHERE attendee_id = ? AND organizer_id = ?");
        $del->bind_param("ii", $attendee_id, $organizer_id);
        $del->execute();
        $del->close();
        echo json_encode(['favourited' => false]);
    } else {
        $ins = $conn->prepare("INSERT INTO favourite_organizers (attendee_id, organizer_id) VALUES (?, ?)");
        $ins->bind_param("ii", $attendee_id, $organizer_id);
        $ins->execute();
        $ins->close();
        echo json_encode(['favourited' => true]);
    }
