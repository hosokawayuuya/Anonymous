<?php
session_start();
require 'db-connect.php';

$room_id = $_GET['room'] ?? '';

try {
    $pdo = connectDB();
    // ゲームに必要な情報を取得し、画面に表示
    // 例: 5×5のカードを表示

    // ここでゲームの状態を初期化、カードの配置などを行う
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="bootstrap.min.css" rel="stylesheet">
    <title>ゲーム開始</title>
</head>
<body>
    <h1>ゲーム開始</h1>
    <div>
        <!-- ここにゲームの5×5のカードを表示するコードを追加 -->
    </div>
</body>
</html>
