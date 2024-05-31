<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$data = json_decode(file_get_contents('php://input'), true);
$roomId = $data['room_id'];

$sql = "UPDATE GameState SET current_turn = IF(current_turn = 'red', 'blue', 'red'), current_role = 'Ope', hint_text = '', hint_count = 0 WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
?>
