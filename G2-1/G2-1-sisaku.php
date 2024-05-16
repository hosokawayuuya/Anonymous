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
        <div>赤の残り枚数: <span id="count-red"><?php echo $colorDistribution['red']; ?></span></div>
        <div>青の残り枚数: <span id="count-blue"><?php echo $colorDistribution['blue']; ?></span></div>
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

    <form action="G2-1-sisaku.php" method="post">
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

    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hint = htmlspecialchars($_POST['hint'], ENT_QUOTES, 'UTF-8');
    $number = $_POST['number'];

    echo $hint . " " . $number;
}
?>


    <script>
        // Initial color counts
        let colorCounts = {
            red: <?php echo $colorDistribution['red']; ?>,
            blue: <?php echo $colorDistribution['blue']; ?>
        };

        function flipCard(card) {
            const color = card.getAttribute('data-color');

            // カードの背景色をデータ属性から取得し、設定
            card.style.backgroundColor = color;

            // 名前の色を変更して見えるように
            card.style.color = 'orange';

            // Decrement the count and update the display
            if (colorCounts[color] !== undefined && colorCounts[color] > 0) {
                colorCounts[color]--;
                document.getElementById(`count-${color}`).innerText = colorCounts[color];
            }
        }
    </script>
</body>
