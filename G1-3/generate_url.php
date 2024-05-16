<?php
session_start();
require 'database.php';

function getRandomWords($pdo) {
    $sql = 'SELECT card_Eng FROM Card ORDER BY RAND() LIMIT 3';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $words = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $words;
}

function createDynamicUrl($pdo) {
    $baseUrl = 'https://Anonymous.game/room/';
    $words = getRandomWords($pdo);
    $path = implode('-', $words);
    return $baseUrl . $path;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = $_POST['nickname'] ?? '';
    if (empty($nickname)) {
        $_SESSION['error_message'] = 'ニックネームを入力してください。';
        header("Location: G1-2.php");
        exit();
    } else {
        $dynamicUrl = createDynamicUrl($pdo);
        $stmt = $pdo->prepare("INSERT INTO Room (entering_ID) VALUES (:entering_ID)");
        $stmt->execute(['entering_ID' => $dynamicUrl]);
        $room_id = $pdo->lastInsertId();

        header("Location: ../G1-3/G1-3.php?room=$room_id");
        exit();
    }
}
?>
