<?php
require '../db-connect.php';

$pdo = new PDO($connect, USER, PASS);

$sql = "DELETE FROM BoardOpe";
$pdo->exec($sql);

resetBoard($pdo);

echo "Game reset successfully";

function resetBoard($pdo) {
    // 色の配分
    $colorDistribution = [
        'red' => 9,
        'blue' => 8,
        'black' => 1,
        'white' => 7
    ];

    // ランダムにカード名を取得
    $sql = "SELECT DISTINCT card_name FROM Card ORDER BY RAND() LIMIT 25";
    $stmt = $pdo->query($sql);
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN); // 25枚のランダムなカード名を取得

    // 色の配列を生成してシャッフル
    $colors = [];
    foreach ($colorDistribution as $color => $count) {
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $color;
        }
    }
    shuffle($colors); // 色の配列をシャッフル

    // カードをボードに挿入
    foreach ($names as $index => $name) {
        $color = $colors[$index];
        $sql = $pdo->prepare('INSERT INTO Board (board_ID, state_ID, card_name, color) VALUES (?, ?, ?, ?)');
        $sql->execute([$index + 1, 2, $name, $color]); // 初期状態は裏 (state_ID = 2) でカードを挿入
    }
}
?>
