<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $room_id = $_POST['room_id'] ?? '';

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO User (room_id) VALUES (:room_id)");
        $stmt->execute(['room_id' => $room_id]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
    } elseif ($action === 'remove' && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("DELETE FROM User WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        unset($_SESSION['user_id']);
    }
}
?>
