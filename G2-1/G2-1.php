
<?php require '../db-connect.php'; ?>
<!--
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Anonymous</title>
  
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <link href="bootstrap.min.css" rel="stylesheet">
  -->
  <?php
// データベースから取得する名前の代わりにハードコーディングされたサンプル名
//$names = ["Alice", "Bob", "Charlie", "David", "Eve", "Frank", "Grace", "Heidi", "Ivan", "Judy", "Mallory", "Niaj", "Olivia", "Peggy", "Sybil", "Trent", "Victor", "Walter", "Xavier", "Yvonne", "Zara", "Oscar", "Liam", "Sophia", "Emma"];
$pdo = new PDO($connect, USER, PASS);

$sql = "SELECT DISTINCT card_name FROM Card ORDER BY RAND() LIMIT 25";
$stmt = $pdo->query($sql);
$names = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 各色の枚数
$colorDistribution = [
    'red' => 9,
    'blue' => 8,
    'black' => 1,
    'white' => 7
];

// 色の配列を生成
$colors = [];
foreach ($colorDistribution as $color => $count) {
    for ($i = 0; $i < $count; $i++) {
        $colors[] = $color;
    }
}

// カードの配列を生成
$cards = [];
for ($i = 0; $i < 25; $i++) {
    // ランダムに名前を選択
    $randomName = $names[array_rand($names)];
    
    // ランダムに色を選択し、配列から削除
    $randomColorKey = array_rand($colors);
    $randomColor = $colors[$randomColorKey];
    unset($colors[$randomColorKey]);
    $colors = array_values($colors); // インデックスをリセット

    // カード配列に追加
    $cards[] = [
        'name' => $randomName,
        'color' => $randomColor
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>5x5 Card Grid</title>
    <style>
        .card-ope {
            width: 100px;
            
            height: 100px;
            display: inline-block;
            margin: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 100px;
            color: orange;
            font-weight: bold;
        }
        .container {
            width: 550px; /* 5 cards * 100px + 4 * 5px (margin) */
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // カードを表示
        foreach ($cards as $card) {
            echo '<button class="card-ope" style="background-color: ' . $card['color'] . ';">' . $card['name'] . '</button>';
        }
        ?>
    </div>
</body>
</html>

