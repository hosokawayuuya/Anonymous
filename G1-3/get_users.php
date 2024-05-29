<?php
require 'db-connect.php';

$room_id = $_GET['room_id'] ?? '';

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT user_name FROM User WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($users as $user) {
        echo "<p>$user</p>";
    }
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>
