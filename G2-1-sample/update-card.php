<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$data = json_decode(file_get_contents('php://input'), true);
$cardId = $data['cardId'];
$roomId = $data['room_id'];

$sql = "UPDATE Board SET state_ID = 1 WHERE board_ID = ? AND room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cardId, $roomId]);
?>
