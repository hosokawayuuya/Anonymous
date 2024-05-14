<?php
session_start();

const SERVER = 'mysql304.phy.lolipop.lan';
const DBNAME = 'LAA1517459-anonymous';
const USER = 'LAA1517459';
const PASS = 'Pass0515';

$dsn = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';

try {
    $pdo = new PDO($dsn, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

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
    $fullUrl = $baseUrl . $path;
    return $fullUrl;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = $_POST['nickname'] ?? '';
    if (empty($nickname)) {
        $_SESSION['error_message'] = 'ニックネームを入力してください。';
        header("Location: G1-2.php");
        exit();
    } else {
        $dynamicUrl = createDynamicUrl($pdo);
        header("Location: ../G1-3/G1-3.html?room=$dynamicUrl");
        exit();
    }
}
?>
