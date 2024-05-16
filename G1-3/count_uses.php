<?php
require 'database.php';

$room_id = $_GET['room_id'] ?? '';

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM User WHERE room_id = :room_id");
$stmt->execute(['room_id' => $room_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['count'];
?>
