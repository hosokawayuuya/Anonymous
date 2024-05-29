<?php
require '../db-connect.php';

// データベース接続を確立
$pdo = connectDB();

// ゲームステートを初期化（もし未初期化の場合）
$sql = "SELECT * FROM GameState WHERE game_id = 1";
$stmt = $pdo->query($sql);
$gameState = $stmt->fetch(PDO::FETCH_ASSOC); // 現在のゲームステートを取得

if (!$gameState) {
    // 初期ゲームステートを挿入
    $sql = "INSERT INTO GameState (game_id, current_turn, current_role, hint_text, hint_count) VALUES (1, 'red', 'Ope', '', 0)";
    $pdo->exec($sql); // 初期のゲームステートをデータベースに挿入
}

// ボードの状態を初期化（もし未初期化の場合）
$sql = "SELECT * FROM BoardOpe";
$stmt = $pdo->query($sql);
$boardExists = $stmt->rowCount() > 0; // ボードが存在するかどうかを確認

if (!$boardExists) {
    // 色の配分
    $colorDistribution = [
        'red' => 9,
        'blue' => 8,
        'black' => 1,
        'white' => 7
    ];

    // ランダムにカード名を取得
    $sql = "SELECT DISTINCT card_name FROM Card ORDER BY RAND() LIMIT 25";
    $stmt = $pdo->query($sql);
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN); // 25枚のランダムなカード名を取得

    // 色の配列を生成してシャッフル
    $colors = [];
    foreach ($colorDistribution as $color => $count) {
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $color;
        }
    }
    shuffle($colors); // 色の配列をシャッフル

    // カードをボードに挿入
    foreach ($names as $index => $name) {
        $color = $colors[$index];
        $sql = $pdo->prepare('INSERT INTO BoardOpe (board_ID, state_ID, card_name, color) VALUES (?, ?, ?, ?)');
        $sql->execute([$index + 1, 2, $name, $color]); // 初期状態は裏 (state_ID = 2) でカードを挿入
    }
} else {
    // 色の配分を取得
    $colorDistribution = [
        'red' => 0,
        'blue' => 0,
        'black' => 0,
        'white' => 0
    ];
    $sql = "SELECT color, COUNT(*) as count FROM BoardOpe WHERE state_ID = 2 GROUP BY color";
    $stmt = $pdo->query($sql);
    $remainingColors = $stmt->fetchAll(PDO::FETCH_ASSOC); // 裏面のカードの色ごとのカウントを取得

    foreach ($remainingColors as $color) {
        $colorDistribution[$color['color']] = $color['count']; // 各色の残り枚数を設定
    }
}

// 現在のゲームステートを取得
$sql = "SELECT * FROM GameState WHERE game_id = 1";
$stmt = $pdo->query($sql);
$gameState = $stmt->fetch(PDO::FETCH_ASSOC); // 再度ゲームステートを取得

// ボードの状態を取得
$sql = "SELECT * FROM BoardOpe";
$stmt = $pdo->query($sql);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC); // ボードの全カードを取得
?>

<!DOCTYPE html>
<html>
<head>
    <title>匿名のゲーム</title>
    <link href="style.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .card {
            width: 100px;
            height: 100px;
            display: inline-block;
            margin: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 100px;
            color: orange;
            background-color: gray; /* 初期背景色 */
            font-weight: bold;
            border: 2px solid black; /* 枠線 */
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

        .counts {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10px;
        }

        .hint-input {
            display: none;
            margin: 20px 0;
        }

        /* ポップアップのスタイル */
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
    <div class="hint-input" id="hint-input">
        <input type="text" id="hint-text" placeholder="ヒントを入力">
        <input type="number" id="hint-count" min="1" max="10" placeholder="枚数">
        <button onclick="submitHint()">完了</button>
    </div>
    <div id="turn-info">現在のターン: <span id="current-turn"><?php echo $gameState['current_turn']; ?></span> チームの <span id="current-role"><?php echo $gameState['current_role']; ?></span></div>
    <div id="hint-display" style="display:none;">
        ヒント: <span id="display-hint-text"></span> | 枚数: <span id="display-hint-count"></span>
    </div>
</div>

<div class="container" style="margin-top: 20px;"> <!-- グリッド間に余白を追加 -->
    <?php $index = 0; ?>
    <?php for ($row = 0; $row < 5; $row++): ?>
        <div class="row">
            <?php for ($col = 0; $col < 5; $col++): ?>
                <?php $card = $cards[$index++]; ?>
                <button class="card" id="card-<?= $card['board_ID']; ?>" data-id="<?= $card['board_ID']; ?>" data-color="<?= $card['color']; ?>" data-name="<?= $card['card_name']; ?>" data-flipped="<?= $card['state_ID'] == 1 ? '1' : '0'; ?>" onclick="flipCard(this)">
                    <?= $card['card_name']; ?>
                </button>
            <?php endfor; ?>
        </div>
    <?php endfor; ?>
</div>

<!-- ポップアップ -->
<div class="overlay" id="overlay"></div>
<div class="popup" id="popup">
    <h2 id="popup-message"></h2>
    <button onclick="closePopup()">閉じる</button>
</div>

<script>
    // 初期の色の枚数を設定
    let colorCounts = {
        red: <?php echo $colorDistribution['red']; ?>,
        blue: <?php echo $colorDistribution['blue']; ?>
    };

    let currentTurn = "<?php echo $gameState['current_turn']; ?>"; // 現在のターンのチームを保持
    let currentRole = "<?php echo $gameState['current_role']; ?>"; // 現在の役割を保持
    let hintCount = <?php echo $gameState['hint_count']; ?>; // 現在のヒントでめくることができる枚数を保持
    let hintText = "<?php echo $gameState['hint_text']; ?>"; // 現在のヒントのテキストを保持

    document.addEventListener("DOMContentLoaded", function () {
        updateTurnInfo(); // ターン情報を更新
        // Opeのターンならヒント入力を表示、Asuのターンならヒント表示を更新
        if (currentRole === 'Ope') {
            document.getElementById('hint-input').style.display = 'block';
        } else {
            document.getElementById('hint-display').style.display = 'block';
            document.getElementById('display-hint-text').innerText = hintText;
            document.getElementById('display-hint-count').innerText = hintCount;
        }
        // 既にめくられたカードを反映
        document.querySelectorAll('.card').forEach(card => {
            if (card.getAttribute('data-flipped') == '1') {
                card.style.backgroundColor = card.getAttribute('data-color');
                card.style.color = 'orange';
                card.disabled = true;
            } else if (currentRole === 'Ope') {
                card.style.backgroundColor = card.getAttribute('data-color');
            }
        });

        // 1秒ごとに状態を更新
        setInterval(fetchGameState, 1000);
    });

    function fetchGameState() {
        $.get('get_game_state.php', function(data) {
            const gameState = JSON.parse(data);
            currentTurn = gameState.current_turn;
            currentRole = gameState.current_role;
            hintCount = gameState.hint_count;
            hintText = gameState.hint_text;

            updateTurnInfo();
        });

        $.get('get_board_state.php', function(data) {
            const boardState = JSON.parse(data);
            boardState.forEach(card => {
                const cardElement = document.getElementById('card-' + card.board_ID);
                cardElement.setAttribute('data-flipped', card.state_ID == 1 ? '1' : '0');
                if (card.state_ID == 1) {
                    cardElement.style.backgroundColor = card.color;
                    cardElement.style.color = 'orange';
                    cardElement.disabled = true;
                } else if (currentRole === 'Ope') {
                    cardElement.style.backgroundColor = card.color;
                } else {
                    cardElement.style.backgroundColor = 'gray';
                }
            });
        });
    }

    function flipCard(card) {
        if (currentRole !== 'Asu') {
            alert("現在の役割ではカードをめくることはできません。"); // Asuでなければカードをめくることはできない
            return;
        }

        const color = card.getAttribute('data-color'); // カードの色を取得
        card.style.backgroundColor = color; // カードの背景色を変更
        card.style.color = 'orange'; // テキストの色を変更
        card.disabled = true; // カードを無効化

        if (color == "black") {
            showPopup(`めくってないチームの勝利です！`); // 黒カードをめくったらゲーム終了
            return;
        }

        if (color === currentTurn) {
            hintCount--;
            if (hintCount === 0) {
                switchTurn(); // ヒントで指定された枚数を全てめくったらターン終了
            }
        } else {
            switchTurn(); // 異なる色のカードをめくったらターン終了
        }

        // カードの色に応じてカウントを減らす
        if (colorCounts[color] !== undefined) {
            colorCounts[color]--;
            document.getElementById(`count-${color}`).innerText = colorCounts[color];
        }

        // カードの状態をデータベースに更新
        const cardId = card.getAttribute('data-id');
        fetch('update-card.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cardId })
        });
    }

    function submitHint() {
        const hintText = document.getElementById('hint-text').value; // ヒントのテキストを取得
        const hintCountInput = document.getElementById('hint-count').value; // ヒントの枚数を取得
        if (hintText.trim() === '' || hintCountInput.trim() === '') {
            alert('ヒントと枚数を入力してください。'); // ヒントと枚数が入力されていなければアラート表示
            return;
        }
        hintCount = parseInt(hintCountInput, 10);
        switchRole(); // 役割を変更
        fetch('update-hint.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ hintText, hintCount }) // ヒントをデータベースに送信
        });
    }

    function switchRole() {
        currentRole = currentRole === 'Ope' ? 'Asu' : 'Ope'; // 役割を切り替え
        document.getElementById('hint-input').style.display = currentRole === 'Ope' ? 'block' : 'none'; // ヒント入力の表示を切り替え
        document.getElementById('hint-display').style.display = currentRole === 'Ope' ? 'none' : 'block'; // ヒント表示の表示を切り替え
        updateTurnInfo(); // ターン情報を更新
    }

    function switchTurn() {
        currentTurn = currentTurn === 'red' ? 'blue' : 'red'; // ターンを切り替え
        currentRole = 'Ope'; // 役割をOpeに変更
        hintCount = 0; // ヒントカウントをリセット
        hintText = ''; // ヒントテキストをリセット
        document.getElementById('hint-input').style.display = 'block'; // ヒント入力を表示
        document.getElementById('hint-display').style.display = 'none'; // ヒント表示を非表示
        updateTurnInfo(); // ターン情報を更新
        fetch('switch-turn.php'); // ターンを切り替えたことをデータベースに送信
    }

    function updateTurnInfo() {
        document.getElementById('current-turn').innerText = currentTurn; // 現在のターンのチームを表示
        document.getElementById('current-role').innerText = currentRole; // 現在の役割を表示
        // Opeのターンならカードの色を表示し、Asuのターンならグレーにする
        document.querySelectorAll('.card').forEach(card => {
            if (card.getAttribute('data-flipped') == '0' && currentRole === 'Asu') {
                card.style.backgroundColor = 'gray';
            } else if (card.getAttribute('data-flipped') == '0' && currentRole === 'Ope') {
                card.style.backgroundColor = card.getAttribute('data-color');
            }
        });
        // ヒント表示を更新
        if (currentRole === 'Asu') {
            document.getElementById('display-hint-text').innerText = hintText;
            document.getElementById('display-hint-count').innerText = hintCount;
        }
    }

    function showPopup(message) {
        document.getElementById('popup-message').innerText = message; // ポップアップにメッセージを表示
        document.getElementById('overlay').style.display = 'block'; // オーバーレイを表示
        document.getElementById('popup').style.display = 'block'; // ポップアップを表示
    }

    function closePopup() {
        document.getElementById('overlay').style.display = 'none'; // オーバーレイを非表示
        document.getElementById('popup').style.display = 'none'; // ポップアップを非表示
        location.reload(); // ページをリロード
    }
</script>
</body>
</html>
