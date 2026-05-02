<?php
    session_start();
    require_once '../db_config.php';
    require_once 'services/email_service.php';

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

        $notif = $conn->prepare("
            SELECT u.email, u.first_name, g.name AS game_name, e.date_time, org.company
            FROM users u
            JOIN events e ON e.id = ?
            JOIN games g ON g.id = e.game_id
            JOIN users org ON org.id = e.organiser_id
            WHERE u.id = ?
        ");
        $notif->bind_param("ii", $event_id, $attendee_id);
        $notif->execute();
        $notif_data = $notif->get_result()->fetch_assoc();
        $notif->close();

        if ($notif_data) {
            send_booking_confirmation(
                $notif_data['email'],
                $notif_data['first_name'],
                $notif_data['game_name'],
                $notif_data['date_time'],
                $notif_data['company']
            );
        }

        echo json_encode(['success' => true]);
    } else {
        $ins->close();
        echo json_encode(['error' => 'Failed to save booking']);
    }
