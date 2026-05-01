<?php

function createEvent($conn, $data)
{
    $stmt = $conn->prepare("
        INSERT INTO events (game_id, organiser_id, date_time)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param(
        "iis",
        $data['game_id'],
        $data['organiser_id'],
        $data['date_time']
    );

    $stmt->execute();
}

function updateEventsBatch($conn, $data)
{
    if (empty($data['ids'])) return;

    $stmt = $conn->prepare("
        UPDATE events
        SET date_time = ?
        WHERE id = ?
    ");

    foreach ($data['ids'] as $i => $id) {
        $stmt->bind_param(
            "si",
            $data['date_time'][$i],
            $id
        );
        $stmt->execute();
    }
}

function deleteEvents($conn, $ids)
{
    if (empty($ids)) return;

    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!is_array($ids) || count($ids) === 0) return;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("DELETE FROM events WHERE id IN ($placeholders)");

    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
}

/**
 * Fetch functions
 */

function getAllEvents($conn)
{
    $result = $conn->query("
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

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getGames($conn)
{
    $result = $conn->query("SELECT id, name FROM games");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getOrganisers($conn)
{
    $result = $conn->query("
        SELECT id, company, first_name, last_name 
        FROM users 
        WHERE role = 'organiser'
    ");

    return $result->fetch_all(MYSQLI_ASSOC);
}