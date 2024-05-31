<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$data = json_decode(file_get_contents('php://input'), true);
$hintText = $data['hintText'];
$hintCount = $data['hintCount'];
$roomId = $data['room_id'];

$sql = "UPDATE GameState SET hint_text = ?, hint_count = ?, current_role = 'Asu' WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hintText, $hintCount, $roomId]);
?>
