<?php
require 'db-connect.php';

$room_id = $_GET['room_id'] ?? '';

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT status FROM Room WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $status = $stmt->fetchColumn();
    echo $status;
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>
