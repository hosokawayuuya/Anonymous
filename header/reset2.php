<?php
require '../db-connect.php'; // db-connect.php ファイルの読み込み

try {
    // データベースに接続する
    $dsn = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8'; // 接続情報を適切に設定する
    $con = new PDO($dsn, USER, PASS); // 正しい接続情報を使用する

    // エラーモードを例外モードに設定
    $con->beginTransaction();

    // 1. Logテーブルをリセット
    $delete_log_sql = "DELETE FROM Log";
    $delete_log_stmt = $con->prepare($delete_log_sql);
    $delete_log_stmt->execute();

    // 2. Roomテーブルをリセット
    $delete_room_sql = "DELETE FROM Room";
    $delete_room_stmt = $con->prepare($delete_room_sql);
    $delete_room_stmt->execute();

    // 3. Boardテーブルをリセット
    $delete_board_sql = "DELETE FROM Board";
    $delete_board_stmt = $con->prepare($delete_board_sql);
    $delete_board_stmt->execute();

    // 4. GameStateテーブルをリセット
    $delete_gamestate_sql = "DELETE FROM GameState";
    $delete_gamestate_stmt = $con->prepare($delete_gamestate_sql);
    $delete_gamestate_stmt->execute();

    // 5. 最後にUserテーブルをリセット
    $delete_user_sql = "DELETE FROM User";
    $delete_user_stmt = $con->prepare($delete_user_sql);
    $delete_user_stmt->execute();

    // すべてのクエリが成功したらコミット
    $con->commit();
} catch (PDOException $e) {
    // エラーが発生した場合はロールバック
    $con->rollBack();
    echo "削除中にエラーが発生しました: " . $e->getMessage();
}
?>
