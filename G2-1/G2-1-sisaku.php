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
        .card-Asu {
            width: 100px;
            height: 100px;
            display: inline-block;
            margin: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 100px;
            color: orange;
            background-color: gray; /* 初期背景色はグレー */
            font-weight: bold;
            border: 2px solid black; /* 枠線を追加 */
            cursor: pointer;
        }

        .card-Ope {
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


        .row {
            display: flex;
            justify-content: center;
        }
        .container {
            width: 560px; /* (100px * 5) + (10px * 5) margin */
            margin: 0 auto;
        }

        .counts {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>

    <div class="counts">
        <div>赤の残り枚数: <span id="count-red">9</span></div>
        <div>青の残り枚数: <span id="count-blue">8</span></div>
    </div>
    
    <div class="container">
    <?php echo "Ope" ?>
    <?php $index = 0; ?>
    <?php for ($row = 0; $row < 5; $row++): ?>
        <div class="row">
            <?php for ($col = 0; $col < 5; $col++): ?>
                <?php $card = $cards[$index++]; ?>
                <button class="card-Ope" style="background-color: <?= $card['color']; ?>;" data-color="<?= $card['color']; ?>">
                    <?= $card['name']; ?>
                </button>
            <?php endfor; ?>
        </div>
    <?php endfor; ?>
</div>

    <div class="container" style="margin-top: 20px;"> <!-- グリッド間に余白を設ける -->

    <?php echo "Asu" ?>
        <?php $index = 0; ?> <!-- インデックスをリセット -->
        <?php for ($row = 0; $row < 5; $row++): ?>
            <div class="row">
                <?php for ($col = 0; $col < 5; $col++): ?>
                    <?php $card = $cards[$index++]; ?>
                    <button class="card-Asu" data-color="<?= $card['color']; ?>" onclick="flipCard(this)">
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
            card.style.color = 'orange';

            if (color === 'red' || color === 'blue') {
                const countElement = document.getElementById('count-' + color);
                let count = parseInt(countElement.textContent);
                countElement.textContent = --count;
            }
        }

        
    </script>
</body>
</html>
