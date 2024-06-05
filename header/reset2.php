<?php
require 'db-connect.php';
 
try {
    // データベースに接続する
    $con = new PDO($connect, USER, PASS);
 

    $delete_sql = "DELETE FROM Log";
    $delete_stmt = $con->prepare($delete_sql);
    $delete_stmt->execute();

    $delete_room_sql = "DELETE FROM Room";
    $delete_room_stmt = $con->prepare($delete_room_sql);
    $delete_room_stmt->execute();

    


 
    echo "すべてのログが正常に削除されました。";
 
    // データベース接続を閉じる
    $con = null;
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>