<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$roomId = $_GET['room_id'];

$sql = "SELECT * FROM Board WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$boardState = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($boardState);
?>
