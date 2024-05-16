<?php require '../db-connect.php'; ?>

<?php
$pdo = new PDO($connect, USER, PASS);

// カード名をランダムに取得
$sql = "SELECT DISTINCT card_name FROM Card ORDER BY RAND() LIMIT 25";
$stmt = $pdo->query($sql);
$names = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 各色の枚数を設定
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
    <title>Anonimous</title>
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

        /* ポップアップウィンドウのスタイル */
        .popup {
            display: none; /* 初期状態では非表示 */
            position: fixed;
            z-index: 1000;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            padding: 20px;
            background: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .popup h2 {
            margin-top: 0;
        }

        .popup button {
            margin-top: 20px;
        }

        .overlay {
            display: none; /* 初期状態では非表示 */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
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

    <!-- ポップアップウィンドウ -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <h2 id="popup-message"></h2>
        <button onclick="closePopup()">閉じる</button>
    </div>

    <script>
        // 初期の色の枚数
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

            //めくったカードが黒だった場合
            if(color == "black"){
                showPopup(`めくってないチームの勝利です！`);
            }

            // 色の枚数を減らし、表示を更新
            if (colorCounts[color] !== undefined && colorCounts[color] > 0) {
                colorCounts[color]--;
                document.getElementById(`count-${color}`).innerText = colorCounts[color];

                // 色の枚数が0になった時に勝敗を表示
                if (colorCounts[color] === 0) {
                    showPopup(`${color}チームの勝利です！`);
                }
            }
        }

        function showPopup(message) {
            document.getElementById('popup-message').innerText = message;
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('popup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('popup').style.display = 'none';
            location.replace("http://aso2201186.boy.jp/Anonymous/G1-2/G1-2.php");
        }
    </script>
</body>
</html>
