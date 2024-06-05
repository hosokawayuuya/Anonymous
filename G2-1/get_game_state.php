<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$roomId = $_GET['room_id'];

$sql = "SELECT * FROM GameState WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$gameState = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($gameState);
?>
