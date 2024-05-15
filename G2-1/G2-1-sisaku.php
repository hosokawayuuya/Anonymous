<?php require '../db-connect.php'; ?>

<?php
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
shuffle($colors);

// カードの配列を生成
$cards = [];
foreach ($names as $name) {
    $randomColor = array_shift($colors);
    $cards[] = [
        'name' => $name,
        'color' => $randomColor
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>5x5 Card Grid</title>
    <link href="style.css" rel="stylesheet">
    <style>
        .card-ope {
            width: 100px;
            height: 100px;
            display: inline-block;
            margin: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 100px;
            color: orange; /* 名前は隠す */
            background-color: gray; /* 初期背景色はグレー */
            font-weight: bold;
            border: 2px solid black; /* 枠線を追加 */
            cursor: pointer;
        }
        .row {
            display: flex;
            justify-content: center;
        }
        .container {
            width: 560px; /* (100px * 5) + (10px * 5) margin */
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php $index = 0; ?>
        <?php for ($row = 0; $row < 5; $row++): ?>
            <div class="row">
                <?php for ($col = 0; $col < 5; $col++): ?>
                    <?php $card = $cards[$index++]; ?>
                    <button class="card-ope" data-color="<?= $card['color']; ?>" onclick="flipCard(this)">
                        <?= $card['name']; ?>
                    </button>
                <?php endfor; ?>
            </div>
        <?php endfor; ?>
    </div>

    <script>
        function flipCard(card) {
            // カードの背景色をデータ属性から取得し、設定
            card.style.backgroundColor = card.getAttribute('data-color');
            // 名前の色を変更して見えるように
            card.style.color = 'white';
        }
    </script>
</body>
</html>
