<?php

function createUser($conn, $data)
{
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
        INSERT INTO users (role, first_name, last_name, company, email, password)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssss",
        $data['role'],
        $data['first_name'],
        $data['last_name'],
        $data['company'],
        $data['email'],
        $passwordHash
    );

    $stmt->execute();
}

/* ─────────────────────────────
  SINGLE UPDATE
───────────────────────────── */
function updateUser($conn, $id, $data)
{
    $stmt = $conn->prepare("
        UPDATE users
        SET role=?, first_name=?, last_name=?, company=?, email=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "sssssi",
        $data['role'],
        $data['first_name'],
        $data['last_name'],
        $data['company'],
        $data['email'],
        $id
    );

    $stmt->execute();
}

/* ─────────────────────────────
  UPDATE 
───────────────────────────── */
function updateUsersBatch($conn, $data)
{
    if (empty($data['ids']) || !is_array($data['ids'])) return;

    foreach ($data['ids'] as $index => $id) {

        $role = $data['role'][$index] ?? null;
        $company = $data['company'][$index] ?? null;
        $email = $data['email'][$index] ?? null;

        if (!$role || !$email) continue;

        $stmt = $conn->prepare("
            UPDATE users
            SET role=?, company=?, email=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "sssi",
            $role,
            $company,
            $email,
            $id
        );

        $stmt->execute();
    }
}

/* ─────────────────────────────
   DELETE
───────────────────────────── */
function deleteUsers($conn, $ids)
{
    if (!is_array($ids) || empty($ids)) return;

    $ids = array_map('intval', $ids);

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    $stmt->execute();
}

function saveUsers($conn)
{
    return true;
}