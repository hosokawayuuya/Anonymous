<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS); // データベース接続を確立
$data = json_decode(file_get_contents('php://input'), true);
$roomId = $data['room_id']; // ルームIDを取得

// 既存のボードを削除
$sql = "DELETE FROM Board WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);

// ボードを再初期化
resetBoard($pdo, $roomId);

echo json_encode(["status" => "success"]);
?>
