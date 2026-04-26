<?php

function createGame($conn, $data)
{
    $stmt = $conn->prepare("
        INSERT INTO games (name, category, description)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $data['name'],
        $data['category'],
        $data['description'] ?? null
    ]);
}

function updateGamesBatch($conn, $data)
{
    if (empty($data['ids'])) return;

    foreach ($data['ids'] as $i => $id) {

        $stmt = $conn->prepare("
            UPDATE games
            SET name = ?,
                category = ?,
                description = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['name'][$i],
            $data['category'][$i],
            $data['description'][$i],
            $id
        ]);
    }
}

function deleteGames($conn, $ids)
{
    if (empty($ids)) return;

    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!is_array($ids) || count($ids) === 0) return;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("DELETE FROM games WHERE id IN ($placeholders)");
    $stmt->execute($ids);
}