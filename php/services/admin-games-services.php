<?php

function createGame($conn, $data)
{
    $name = $data['name'];
    $category = $data['category'];
    $description = $data['description'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO games (name, category, description)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sss", $name, $category, $description);
    $stmt->execute();
}

function updateGamesBatch($conn, $data)
{
    if (empty($data['ids'])) return;

    $stmt = $conn->prepare("
        UPDATE games
        SET name = ?, category = ?, description = ?
        WHERE id = ?
    ");

    foreach ($data['ids'] as $i => $id) {

        $name = $data['name'][$i] ?? '';
        $category = $data['category'][$i] ?? '';
        $description = $data['description'][$i] ?? null;

        $stmt->bind_param("sssi", $name, $category, $description, $id);
        $stmt->execute();
    }
}

function deleteGames($conn, $ids)
{
    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!is_array($ids) || empty($ids)) return;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("DELETE FROM games WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
}