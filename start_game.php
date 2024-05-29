<?php
require 'db-connect.php';

$room_id = $_POST['room_id'] ?? '';

try {
    $pdo = connectDB();
    // ゲーム開始に必要な処理をここに追加
    $stmt = $pdo->prepare("UPDATE Room SET status = 'started' WHERE room_ID = ?");
    $stmt->execute([$room_id]);

    echo "ゲームが開始されました";
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>
