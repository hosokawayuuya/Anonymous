<?php
// データベースの接続情報を含む変数を読み込む
require '../db-connect.php';

try {
    // データベースに接続する
    $con = new PDO($connect, USER, PASS);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // フォームデータの取得
        $hint = $_POST['hint'];
        $sheet = $_POST['number'];
        $user_ID = NULL; // NULLのまま使用します

        // データベースにデータを挿入するSQLクエリの作成
        $sql = "INSERT INTO Log (user_ID, hint, sheet) VALUES (?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->execute([$user_ID, $hint, $sheet]);

        // 全てのデータを取得するSQLクエリの作成
        $select_all_sql = "SELECT user_ID, hint, sheet FROM Log";
        $select_all_stmt = $con->query($select_all_sql);
        $all_results = $select_all_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($all_results) > 0) {
            // 出力データの表示
            echo "<h3>ログ:</h3>";
            echo "<div style='display: table;'>";
            foreach ($all_results as $key => $row) {
                $username_color = ($key % 2 == 0) ? 'red' : 'blue';
                echo "<div style='display: table-row;'>";
                echo "<div style='display: table-cell; width: 100px; color: $username_color;'>" . htmlspecialchars($row["user_ID"] ?? '') . "</div>";
                echo "<div style='display: table-cell; width: 200px;'>ヒント: " . htmlspecialchars($row["hint"] ?? '') . "</div>";
                echo "<div style='display: table-cell; width: 100px;'>枚数: " . htmlspecialchars($row["sheet"] ?? '') . "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "データがありません。";
        }
    } else {
        echo "フォームデータが送信されていません。";
    }

    $num = intval($sheet) + 1;
    // データベース接続を閉じる
    $con = null;

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>
