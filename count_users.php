<?php
require 'db-connect.php';

$room_id = $_GET['room_id'] ?? '';

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM User WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    echo $stmt->fetchColumn();
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>
