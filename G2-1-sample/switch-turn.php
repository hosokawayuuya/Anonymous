<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$sql = "SELECT current_turn FROM GameState WHERE game_id = 1";
$stmt = $pdo->query($sql);
$currentTurn = $stmt->fetchColumn();

$newTurn = $currentTurn === 'red' ? 'blue' : 'red';

$sql = $pdo->prepare('UPDATE GameState SET current_turn = ?, current_role = "Ope", hint_count = 0 WHERE game_id = 1');
$sql->execute([$newTurn]);
?>
