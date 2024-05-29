<?php
session_start();
require 'db-connect.php';

$room_id = $_POST['room_id'] ?? '';
$role_id = $_POST['role_id'] ?? '';
$team_id = $_POST['team_id'] ?? '';
$nickname = $_SESSION['nickname'] ?? '';

try {
    $pdo = connectDB();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM User WHERE room_ID = ? AND team_ID = ? AND role_ID = ?");
    $stmt->execute([$room_id, $team_id, $role_id]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("UPDATE User SET role_ID = ?, team_ID = ? WHERE room_ID = ? AND user_name = ?");
        $stmt->execute([$role_id, $team_id, $room_id, $nickname]);
        $pdo->commit();
        echo "役割が設定されました";
    } else {
        echo "この役割は既に選択されています";
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo 'エラー: ' . $e->getMessage();
}
?>
