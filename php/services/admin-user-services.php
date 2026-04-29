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

function updateUsersBatch($conn, $data)
{
    if (empty($data['ids'])) return;

    foreach ($data['ids'] as $i => $id) {

        $role = $data['role'][$i];
        $company = $data['company'][$i] ?? null;
        $email = $data['email'][$i];

        $sql = "UPDATE users SET role=?, company=?, email=?";
        $params = [$role, $company, $email];
        $types = "sss";

        if (!empty($data['password'][$i])) {
            $sql .= ", password=?";
            $params[] = password_hash($data['password'][$i], PASSWORD_BCRYPT);
            $types .= "s";
        }

        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
}

function deleteUsers($conn, $ids)
{
    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!is_array($ids) || empty($ids)) return;

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