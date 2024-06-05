<?php
session_start();
require '../db-connect.php';

$roomId = $_GET['room'] ?? '';

// データベース接続を確立
try {
    $pdo = connectDB();
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// ゲームステートを初期化（もし未初期化の場合）
$sql = "SELECT * FROM GameState WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$gameState = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gameState) {
    // 初期ゲームステートを挿入
    $sql = "INSERT INTO GameState (room_ID, current_turn, current_role, hint_text, hint_count) VALUES (?, 'red', 'Ope', '', 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roomId]);
}

// ボードの状態を初期化（もし未初期化の場合）
$sql = "SELECT * FROM Board WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$boardExists = $stmt->rowCount() > 0;

if (!$boardExists) {
    resetBoard($pdo, $roomId);
}

function resetBoard($pdo, $roomId) {
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
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 色の配列を生成してシャッフル
    $colors = [];
    foreach ($colorDistribution as $color => $count) {
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $color;
        }
    }
    shuffle($colors);

    // カードをボードに挿入
    foreach ($names as $index => $name) {
        $color = $colors[$index];
        $sql = $pdo->prepare('INSERT INTO Board (board_ID, state_ID, card_name, color, room_ID) VALUES (?, ?, ?, ?, ?)');
        $sql->execute([$index + 1, 2, $name, $color, $roomId]);
    }
}

// 現在のゲームステートを取得
$sql = "SELECT * FROM GameState WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$gameState = $stmt->fetch(PDO::FETCH_ASSOC);

// ボードの状態を取得
$sql = "SELECT * FROM Board WHERE room_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 色の配分を再取得
$colorDistribution = [
    'red' => 0,
    'blue' => 0,
    'black' => 0,
    'white' => 0
];
$sql = "SELECT color, COUNT(*) as count FROM Board WHERE room_ID = ? AND state_ID = 2 GROUP BY color";
$stmt = $pdo->prepare($sql);
$stmt->execute([$roomId]);
$remainingColors = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($remainingColors as $color) {
    $colorDistribution[$color['color']] = $color['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>匿名のゲーム</title>
    <link href="style.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="counts">
    <div>赤の残り枚数: <span id="count-red"><?php echo $colorDistribution['red']; ?></span></div>
    <div>青の残り枚数: <span id="count-blue"><?php echo $colorDistribution['blue']; ?></span></div>
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

<div class="container">
    <div class="hint-input" id="hint-input">
        <input type="text" id="hint-text" placeholder="ヒントを入力">
        <input type="number" id="hint-count" min="1" max="10" placeholder="枚数">
        <button onclick="submitHint()">完了</button>
    </div>
    <div class="end-turn" id="end-turn" style="display:none;">
        <button onclick="endTurn()">推測終了</button>
    </div>

    <div id="turn-info">現在のターン: <span id="current-turn"><?php echo $gameState['current_turn']; ?></span> チームの <span id="current-role"><?php echo $gameState['current_role']; ?></span></div>
    <div id="hint-display" style="display:none;">
        ヒント: <span id="display-hint-text"></span> | 枚数: <span id="display-hint-count"></span>
    </div>
</div>

<!-- ポップアップ -->
<div class="overlay" id="overlay"></div>
<div class="popup" id="popup">
    <h2 id="popup-message"></h2>
    <button onclick="startNewGame()">新しいゲームを開始する</button>
    <button onclick="goBack()">戻る</button>
</div>

<script>
    const roomId = <?php echo json_encode($roomId); ?>;

    let colorCounts = {
        red: <?php echo $colorDistribution['red']; ?>,
        blue: <?php echo $colorDistribution['blue']; ?>
    };

    let currentTurn = "<?php echo $gameState['current_turn']; ?>";
    let currentRole = "<?php echo $gameState['current_role']; ?>";
    let hintCount = <?php echo $gameState['hint_count']; ?>;
    let hintText = "<?php echo $gameState['hint_text']; ?>";

    document.addEventListener("DOMContentLoaded", function () {
        updateTurnInfo();
        if (currentRole === 'Ope') {
            document.getElementById('hint-input').style.display = 'block';
        } else {
            document.getElementById('hint-display').style.display = 'block';
            document.getElementById('end-turn').style.display = 'block';
            document.getElementById('display-hint-text').innerText = hintText;
            document.getElementById('display-hint-count').innerText = hintCount;
        }
        document.querySelectorAll('.card').forEach(card => {
            if (card.getAttribute('data-flipped') == '1') {
                card.style.backgroundColor = adjustColor(card.getAttribute('data-color'), 0.7);
                card.style.color = 'orange';
                card.disabled = true;
            } else if (currentRole === 'Ope') {
                card.style.backgroundColor = card.getAttribute('data-color');
            }
        });

        setInterval(fetchGameState, 1000);
    });

    function fetchGameState() {
        $.get('get_game_state.php', { room_id: roomId }, function(data) {
            const gameState = JSON.parse(data);
            currentTurn = gameState.current_turn;
            currentRole = gameState.current_role;
            hintCount = gameState.hint_count;
            hintText = gameState.hint_text;

            updateTurnInfo();
        });

        $.get('get_board_state.php', { room_id: roomId }, function(data) {
            const boardState = JSON.parse(data);
            boardState.forEach(card => {
                const cardElement = document.getElementById('card-' + card.board_ID);
                cardElement.setAttribute('data-flipped', card.state_ID == 1 ? '1' : '0');
                if (card.state_ID == 1) {
                    const color = cardElement.getAttribute('data-color');
                    const colorImageMap = {
                        'red': 'red.webp',
                        'blue': 'blue.webp',
                        'black': 'black.webp',
                        'white': 'white.webp'
                    };

                    const imgSrc = `../img/${colorImageMap[color]}`;
                    cardElement.style.backgroundImage = `url('${imgSrc}')`;
                    cardElement.style.backgroundSize = 'cover';
                    cardElement.style.color = 'orange';
                    cardElement.disabled = true;
                } else if (currentRole === 'Ope') {
                    cardElement.style.backgroundColor = card.getAttribute('data-color');
                } else {
                    cardElement.style.backgroundColor = 'gray';
                }
            });
        });
    }

    function flipCard(card) {
        if (currentRole !== 'Asu') {
            alert("現在の役割ではカードをめくることはできません。");
            return;
        }

        const color = card.getAttribute('data-color');
        const colorImageMap = {
            'red': 'red.webp',
            'blue': 'blue.webp',
            'black': 'black.webp',
            'white': 'white.webp'
        };

        const imgSrc = `../img/${colorImageMap[color]}`;
        card.style.backgroundImage = `url('${imgSrc}')`;
        card.style.backgroundSize = 'cover';
        card.style.color = 'orange';
        card.disabled = true;
        card.innerText = '';

        if (color == "black") {
            showPopup(`めくってないチームの勝利です！`);
            resetGameState();
            return;
        }

        if (color === currentTurn) {
            hintCount--;
            if (hintCount === 0) {
                switchTurn();
            }
        } else {
            switchTurn();
        }

        if (colorCounts[color] !== undefined) {
            colorCounts[color]--;
            document.getElementById(`count-${color}`).innerText = colorCounts[color];
        }

        checkForWin();

        const cardId = card.getAttribute('data-id');
        fetch('update-card.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cardId, room_id: roomId })
        });
    }

    function submitHint() {
        const hintText = document.getElementById('hint-text').value;
        const hintCountInput = document.getElementById('hint-count').value;
        if (hintText.trim() === '' || hintCountInput.trim() === '') {
            alert('ヒントと枚数を入力してください。');
            return;
        }
        hintCount = parseInt(hintCountInput, 10);
        switchRole();
        fetch('update-hint.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ hintText, hintCount, room_id: roomId })
        });
    }

    function switchRole() {
        currentRole = currentRole === 'Ope' ? 'Asu' : 'Ope';
        document.getElementById('hint-input').style.display = currentRole === 'Ope' ? 'block' : 'none';
        document.getElementById('hint-display').style.display = currentRole === 'Ope' ? 'none' : 'block';
        document.getElementById('end-turn').style.display = currentRole === 'Ope' ? 'none' : 'block';
        updateTurnInfo();
    }

    function switchTurn() {
        currentTurn = currentTurn === 'red' ? 'blue' : 'red';
        currentRole = 'Ope';
        hintCount = 0;
        hintText = '';
        document.getElementById('hint-input').style.display = 'block';
        document.getElementById('hint-display').style.display = 'none';
        document.getElementById('end-turn').style.display = 'none';
        updateTurnInfo();
        fetch('switch-turn.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ room_id: roomId })
        });
    }

    function updateTurnInfo() {
        document.getElementById('current-turn').innerText = currentTurn;
        document.getElementById('current-role').innerText = currentRole;
        document.querySelectorAll('.card').forEach(card => {
            if (card.getAttribute('data-flipped') == '0' && currentRole === 'Asu') {
                card.style.backgroundColor = 'gray';
            } else if (card.getAttribute('data-flipped') == '0' && currentRole === 'Ope') {
                card.style.backgroundColor = card.getAttribute('data-color');
            }
        });
        if (currentRole === 'Asu') {
            document.getElementById('display-hint-text').innerText = hintText;
            document.getElementById('display-hint-count').innerText = hintCount;
            document.getElementById('end-turn').style.display = 'block';
        } else {
            document.getElementById('end-turn').style.display = 'none';
        }
    }

    function checkForWin() {
        if (colorCounts.red === 0) {
            showPopup("赤チームの勝利です！");
            resetGameState();
        } else if (colorCounts.blue === 0) {
            showPopup("青チームの勝利です！");
            resetGameState();
        }
    }

    function resetGameState() {
        fetch('reset-game-state.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ room_id: roomId })
        });
    }

    function startNewGame() {
        fetch('reset-board.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ room_id: roomId })
        }).then(() => {
            location.reload();
        });
    }

    function goBack() {
        window.location.href = '../G1-2/G1-2.php';
    }

    function adjustColor(color, factor) {
        const colorMap = {
            "red": "#FF9999",
            "blue": "#9999FF",
            "black": "#999999",
            "white": "#CCCCCC"
        };
        return colorMap[color] || color;
    }

    function showPopup(message) {
        document.getElementById('popup-message').innerText = message;
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('popup').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('popup').style.display = 'none';
    }

    function endTurn() {
        switchTurn();
    }
</script>
</body>
</html>
