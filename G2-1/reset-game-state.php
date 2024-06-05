<?php require '../db-connect.php'; ?>

<?php
$pdo = new PDO($connect, USER, PASS); // データベース接続を確立

$data = json_decode(file_get_contents('php://input'), true);
$roomId = $data['room_id'];

// ゲームステートをリセット
$sql = "UPDATE GameState SET current_turn = 'red', current_role = 'Ope', hint_text = '', hint_count = 0 WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);

echo json_encode(['status' => 'success']);
?>
