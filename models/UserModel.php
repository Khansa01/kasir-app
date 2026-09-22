<?php
function getAllUsers($pdo) {
    return $pdo->query("SELECT * FROM users ORDER BY role, nama")->fetchAll();
}

function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserByUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function createUser($pdo, $nama, $username, $password, $role) {
    $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)");
    return $stmt->execute([$nama, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
}

function updateUser($pdo, $id, $nama, $username, $role, $password = null) {
    if ($password) {
        $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id=?");
        return $stmt->execute([$nama, $username, password_hash($password, PASSWORD_DEFAULT), $role, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id=?");
        return $stmt->execute([$nama, $username, $role, $id]);
    }
}

function deleteUser($pdo, $id) {
    return $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
}