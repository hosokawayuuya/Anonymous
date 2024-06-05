
<?php
require '../db-connect.php';

// POSTリクエストからデータを取得
$name = $_POST['name'];
$state_id = $_POST['state_id'];

echo "Received name: $name, state_id: $state_id"; // デバッグメッセージの追加

try {
    // データベースに接続
    $pdo = new PDO($connect, USER, PASS);

    // 更新クエリの実行
    $sql = "UPDATE Board SET state_ID = :state_id WHERE card_name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':state_id' => $state_id, ':name' => $name]); // パラメータを修正

    // 成功メッセージを返す
    echo "データベースが更新されました";
} catch (PDOException $e) {
    // エラーメッセージを表示
    echo "エラー: " . $e->getMessage();
}
?>
