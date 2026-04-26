<?php

function createUser($conn, $data)
{
    $stmt = $conn->prepare("
        INSERT INTO users (role, first_name, last_name, company, email, password)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['role'],
        $data['first_name'] ?: null,
        $data['last_name'] ?: null,
        $data['company'] ?: null,
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT)
    ]);
}

/**
 * SINGLE UPDATE (fallback)
 */
function updateUser($conn, $id, $data)
{
    $stmt = $conn->prepare("
        UPDATE users
        SET role=?, first_name=?, last_name=?, company=?, email=?
        WHERE id=?
    ");

    $stmt->execute([
        $data['role'],
        $data['first_name'] ?: null,
        $data['last_name'] ?: null,
        $data['company'] ?: null,
        $data['email'],
        $id
    ]);
}

/**
 * BATCH UPDATE (NEW)
 */
function updateUsersBatch($conn, $data)
{
    $ids = $data['ids'] ?? [];
    $roles = $data['role'] ?? [];
    $companies = $data['company'] ?? [];
    $emails = $data['email'] ?? [];

    foreach ($ids as $i => $id) {

        $stmt = $conn->prepare("
            UPDATE users
            SET role=?, company=?, email=?
            WHERE id=?
        ");

        $stmt->execute([
            $roles[$i] ?? 'attendee',
            $companies[$i] ?: null,
            $emails[$i],
            $id
        ]);
    }
}

/**
 * DELETE
 */
function deleteUsers($conn, $ids)
{
    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!is_array($ids) || empty($ids)) return;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
    $stmt->execute($ids);
}

function saveUsers($conn)
{
    return true;
}