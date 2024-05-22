<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>データベースへのデータ保存フォーム</title>
</head>
<body>
    <form action="G2-1-hint-output.php" method="post">
        ヒントを入力：
        <input type="text" name="hint" required>

        <select name="number" required>
            <option value="0">-</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
        </select>
        <button type="submit">送信</button>
    </form>
</body>
</html>
