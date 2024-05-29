<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$currentTurn = '';
$currentRole = 'Ope';

$sql = "SELECT current_turn FROM GameState WHERE game_id = 1";
$stmt = $pdo->query($sql);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    $currentTurn = $result['current_turn'] == 'red' ? 'blue' : 'red';
}

$sql = $pdo->prepare("UPDATE GameState SET current_turn = ?, current_role = ?, hint_text = '', hint_count = 0 WHERE game_id = 1");
$sql->execute([$currentTurn, $currentRole]);
?>
