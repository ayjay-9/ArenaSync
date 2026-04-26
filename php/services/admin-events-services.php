<?php

function createEvent($conn, $data)
{
    $stmt = $conn->prepare("
        INSERT INTO events (game_id, organiser_id, date_time)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $data['game_id'],
        $data['organiser_id'],
        $data['date_time']
    ]);
}

function updateEventsBatch($conn, $data)
{
    if (empty($data['ids'])) return;

    foreach ($data['ids'] as $i => $id) {

        $stmt = $conn->prepare("
            UPDATE events
            SET date_time = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['date_time'][$i],
            $id
        ]);
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

    $stmt = $conn->prepare("DELETE FROM events WHERE id IN ($placeholders)");
    $stmt->execute($ids);
}