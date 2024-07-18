<?php
session_start();
require '../db-connect.php';

$room_id = $_GET['room_id'] ?? '';
$role_id = $_SESSION['role_id'] ?? '';
$team_id = $_SESSION['team_id'] ?? '';

if (empty($room_id) || empty($role_id) || empty($team_id)) {
    echo 'Room ID, role ID, or team ID is missing';
    exit();
}

$_SESSION['room_id'] = $room_id;

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM Board WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $board = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$board) {
        throw new Exception('Board data not found');
    }

    $stmt = $pdo->prepare("SELECT color, COUNT(*) as count FROM Board WHERE room_ID = ? AND state_ID = 2 GROUP BY color");
    $stmt->execute([$room_id]);
    $color_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $counts = [
        'red' => 0,
        'blue' => 0,
        'black' => 0,
        'white' => 0
    ];

    foreach ($color_counts as $count) {
        $counts[$count['color']] = $count['count'];
    }

    $red_count = $counts['red'];
    $blue_count = $counts['blue'];

    $is_astronaut = ($role_id == 2);

    $stmt = $pdo->prepare("SELECT current_team, current_role, hint_text, hint_count FROM GameState WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $current_state = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_state) {
        throw new Exception('Game state not found');
    }

    $current_team = $current_state['current_team'];
    $current_role = $current_state['current_role'];
    $hint_text = $current_state['hint_text'];
    $hint_count = $current_state['hint_count'];

    $is_current_turn = ($current_team == $team_id && $current_role == $role_id);
    $original_hint_count = $hint_count; // ここでオリジナルのヒント枚数を保持

    // ユーザー数を取得してセッションに保存
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM User WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $userCount = $stmt->fetchColumn();
    $_SESSION['user_count'] = $userCount; // セッションに参加人数を保存

} catch (Exception $e) {
    echo 'エラー: ' . $e->getMessage();
    exit();
}

function getRandomImage($color) {
    $images = [
        'red' => ['red.webp'],
        'blue' => ['blue.webp'],
        'black' => ['black.webp'],
        'white' => ['white.webp']
    ];
    $imageList = $images[$color] ?? ['default.webp'];
    return $imageList[array_rand($imageList)];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Game</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            let selectedCardId = null;
            let gameEnded = false;
            let isMyTurn = <?php echo json_encode($is_current_turn); ?>; // 自分のターンかどうかのフラグ

            function attachCardClickHandlers() {
                if (gameEnded) return; // ゲーム終了後は操作を無効にする
                $('.card').off('click').on('click', function() {
                    <?php if ($is_current_turn && $role_id == 2): ?>
                        selectedCardId = $(this).data('card-id');
                        $('#overlay').addClass('active');
                        $('#flip-popup').addClass('active');
                    <?php else: ?>
                        alert('あなたの役割ではカードをめくることはできません');
                    <?php endif; ?>
                });
            }

            attachCardClickHandlers();

            $('#confirm-flip').click(function() {
                if (selectedCardId) {
                    $.post('flip_card.php', {card_id: selectedCardId, room_id: '<?php echo $room_id; ?>'}, function(response) {
                        if (response.status === 'success') {
                            updateBoard();
                            if (response.reload) {
                                location.reload(); // カードをめくった直後にページをリロード
                            }
                        } else if (response.status === 'win') {
                            gameEnded = true;
                            $('#win-message').text(response.message);
                            $('#flip-popup').hide();
                            $('#win-popup').show();
                            $('#overlay').show(); // 勝利時のポップアップを表示
                        } else {
                            alert(response.message);
                        }
                        $('#overlay').removeClass('active');
                        $('#flip-popup').removeClass('active');
                    }, 'json');
                }
            });

            $('#cancel-flip').click(function() {
                $('#overlay').removeClass('active');
                $('#flip-popup').removeClass('active');
            });

            $('#return-to-room').click(function() {
                window.location.href = '../G1-2/G1-2.php'; // ルーム作成に戻る
            });

            $('#hint-form').submit(function(e) {
                e.preventDefault();
                const hint = $('#hint').val();
                const hintCount = $('#hint-count').val();
                $.post('submit_hint.php', {room_id: '<?php echo $room_id; ?>', hint: hint, hint_count: hintCount}, function(response) {
                    if (response.status === 'success') {
                        updateBoard();
                        location.reload(); // ヒントを送信した直後にページをリロード
                    } else {
                        alert(response.message);
                    }
                }, 'json');
            });

            $('#end-turn').click(function() {
                $.post('end_turn.php', {room_id: '<?php echo $room_id; ?>'}, function(response) {
                    if (response.status === 'success') {
                        isMyTurn = false; // 自分のターンが終わる
                        updateBoard();
                        location.reload(); // 自身のターンが終わった直後にページをリロード
                    } else {
                        alert(response.message);
                    }
                }, 'json');
            });

            function updateBoard() {
                if (isMyTurn) return; // 自分のターンなら更新をスキップ

                $.get('get_board.php', {room_id: '<?php echo $room_id; ?>'}, function(response) {
                    console.log('Board response:', response); // デバッグログ追加
                    if (response.status === 'success') {
                        $('#game-board').html(response.board);
                        $('#red-count').text(response.red_count);
                        $('#blue-count').text(response.blue_count);
                        attachCardClickHandlers();
                    } else {
                        console.error(response.message);
                    }
                }, 'json');

                $.get('get_game_state.php', {room_id: '<?php echo $room_id; ?>'}, function(response) {
                    console.log('Game state response:', response); // デバッグログ追加
                    if (response.status === 'success') {
                        // ターンが切り替わったらページをリロード
                        if (!isMyTurn && response.game_state.current_team == <?php echo json_encode($team_id); ?> && response.game_state.current_role == <?php echo json_encode($role_id); ?>) {
                            location.reload();
                        }
                        // 現在のチーム・役割のUIを更新
                        $('#current-team-role').text((response.game_state.current_team == 1 ? '赤' : '青') + 'チームの' + (response.game_state.current_role == 1 ? 'オペレーター' : 'アストロノーツ'));
                        // ゲームのログを更新
                        updateLog();
                    } else {
                        console.error(response.message);
                    }
                }, 'json');
            }

            function updateLog() {
                $.get('get_log.php', {room_id: '<?php echo $room_id; ?>'}, function(response) {
                    if (response.status === 'success') {
                        let logHtml = '';
                        response.logs.forEach(log => {
                            logHtml += '<tr><td>' + escapeHtml(log.team_name) + '</td><td>' + escapeHtml(log.hint) + '</td><td>' + escapeHtml(log.sheet) + '</td></tr>';
                        });
                        $('#log-table tbody').html(logHtml);
                    } else {
                        console.error(response.message);
                    }
                }, 'json');
            }

            function escapeHtml(string) {
                return String(string).replace(/[&<>"'`=\/]/g, function (s) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;',
                        '/': '&#x2F;',
                        '=': '&#x3D;',
                        '`': '&#x60;'
                    }[s];
                });
            }

            // 定期的に更新を確認する
            setInterval(updateBoard, 500);

            // 勝利チェックを定期的に行う
            setInterval(checkWin, 1000);

            function checkWin() {
                $.get('win_check.php', {room_id: '<?php echo $room_id; ?>'}, function(response) {
                    if (response.status === 'win') {
                        gameEnded = true;
                        $('#win-message').text(response.message);
                        $('#flip-popup').hide();
                        $('#win-popup').show();
                        $('#overlay').show(); // 勝利時のポップアップを表示
                    }
                }, 'json');
            }
        });
    </script>
</head>
<body>
<div class="container">
    <div class="team-box red-team">
        <div class="photo-container">
            <img src="../img/universe3.jpg" alt="赤チーム写真" class="team-photo">
            <span class="number">9</span>
        </div>
        <div class="team-info">
            <p>オペレーター</p>
            <br>
            <p>アストロノーツ</p>
        </div>
    </div>

    <div id="game-board">
        <?php
        $cards_per_row = 5;
        $total_cards = count($board);

        for ($i = 0; $i < $total_cards; $i += $cards_per_row) {
            echo '<div class="row">';
            for ($j = 0; $j < $cards_per_row; $j++) {
                if ($i + $j < $total_cards) {
                    $card = $board[$i + $j];
                    if ($card['state_ID'] == 1) {
                        $background_image = getRandomImage($card['color']);
                        echo '<div class="card" data-card-id="' . $card['board_ID'] . '" style="background-image: url(../img/' . $background_image . ');"></div>';
                    } else {
                        $background_color = $is_astronaut ? 'gray' : $card['color'];
                        echo '<div class="card" data-card-id="' . $card['board_ID'] . '" style="background-color: ' . $background_color . ';">' . $card['card_name'] . '</div>';
                    }
                }
            }
            echo '</div>';
        }
        ?>
    </div>

    <div class="team-box blue-team">
        <div class="photo-container">
            <img src="../img/universe4.jpg" alt="青チーム写真" class="team-photo">
        </div>
        <div class="team-info">
            <p>オペレーター</p>
            <br>
            <p>アストロノーツ</p>
        </div>
    </div>
</div>
    <?php if ($is_current_turn && $role_id == 1): ?>
        <div class="hint-input">
            <form id="hint-form">
                <label for="hint">ヒント:</label>
                <input type="text" id="hint" name="hint" required>
                <label for="hint-count">枚数:</label>
                <select id="hint-count" name="hint-count" required>
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit">送信</button>
            </form>
        </div>
    <?php elseif ($is_current_turn && $role_id == 2): ?>
        <div class="hint-display">
            <p>ヒント: <?php echo htmlspecialchars($hint_text); ?></p>
            <p>めくれる枚数: 残り<?php echo htmlspecialchars($original_hint_count + 1); ?>枚</p>
            <button id="end-turn">推測終了</button>
        </div>
    <?php else: ?>
        <p>現在のターンではありません。待機してください。</p>
    <?php endif; ?>
    <div class="log">
        <h3>宇宙遊泳記録</h3>
        <table id="log-table">
            <thead>
                <tr>
                    <th>チーム</th>
                    <th>ヒント</th>
                    <th>枚数</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("SELECT t.team_name, l.hint, l.sheet FROM Log l JOIN User u ON l.user_ID = u.user_ID JOIN Team t ON u.team_ID = t.team_ID WHERE l.room_ID = ? ORDER BY l.log_ID DESC");
                $stmt->execute([$room_id]);
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($logs as $log) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($log['team_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($log['hint']) . '</td>';
                    echo '<td>' . htmlspecialchars($log['sheet']) . '</td>';
                    echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="flip-popup">
        <p>カードをめくりますか？</p>
        <button id="confirm-flip">はい</button>
        <button id="cancel-flip">いいえ</button>
    </div>
    <div class="popup" id="win-popup">
        <p id="win-message"></p>
        <button id="return-to-room">ルーム作成に戻る</button>
    </div>
</body>
</html>
