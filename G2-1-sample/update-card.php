<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);
$data = json_decode(file_get_contents('php://input'), true);
$cardId = $data['cardId'];

$sql = $pdo->prepare('UPDATE BoardOpe SET state_ID = 1 WHERE board_ID = ?');
$sql->execute([$cardId]);
?>
