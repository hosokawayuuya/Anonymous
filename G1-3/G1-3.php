<?php
session_start();
require '../db-connect.php';

$room_id = $_GET['room'] ?? '';
$nickname = $_SESSION['nickname'] ?? '';
$is_host = $_SESSION['is_host'] ?? false;

if (!$is_host && empty($nickname) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nickname'])) {
    $nickname = htmlspecialchars($_POST['nickname'], ENT_QUOTES, 'UTF-8');

    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("INSERT INTO User (room_ID, user_name, team_ID, role_ID) VALUES (?, ?, NULL, NULL)");
        $stmt->execute([$room_id, $nickname]);

        $_SESSION['nickname'] = $nickname;

        header("Location: G1-3.php?room=$room_id");
        exit();
    } catch (PDOException $e) {
        echo 'データベース接続エラー: ' . $e->getMessage();
        exit();
    }
}

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT user_name, team_ID, role_ID FROM User WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM User WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $userCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT status FROM Room WHERE room_ID = ?");
    $stmt->execute([$room_id]);
    $roomStatus = $stmt->fetchColumn();
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

$teamNames = [1 => '赤チーム', 2 => '青チーム'];
$roleNames = [1 => 'オペレーター', 2 => 'アストロノーツ'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <title>役割選択</title>
    <style>
        .disabled-button {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const roomId = "<?php echo htmlspecialchars($room_id); ?>";

            function updateUsers() {
                $.get('get_users.php', {room_id: roomId}, function(data) {
                    $('#userList').html(data);
                    updateRoleButtons(); // ボタンの状態も更新
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAXエラー: " + textStatus + ", " + errorThrown);
                });
            }

            function updateUserCount() {
                $.get('../count_users.php', {room_id: roomId}, function(data) {
                    $('#userCount').text(data);
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAXエラー: " + textStatus + ", " + errorThrown);
                });
            }

            function updateRoleButtons() {
                $.get('get_role_status.php', {room_id: roomId}, function(data) {
                    const result = JSON.parse(data);
                    const roles = result.roles;
                    const allRolesSelected = result.allRolesSelected;

                    $('.role-button').each(function() {
                        const roleId = $(this).data('role-id');
                        const teamId = $(this).data('team-id');
                        const isSelected = roles.some(role => role.team_ID == teamId && role.role_ID == roleId);
                        if (isSelected) {
                            $(this).addClass('disabled-button').prop('disabled', true);
                        } else {
                            $(this).removeClass('disabled-button').prop('disabled', false);
                        }
                    });

                    if (allRolesSelected) {
                        $('#startGame').show();
                    } else {
                        $('#startGame').hide();
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAXエラー: " + textStatus + ", " + errorThrown);
                });
            }

            function checkGameStart() {
                $.get('../check_game_start.php', {room_id: roomId}, function(data) {
                    if (data === 'started') {
                        window.location.href = '../G2-1/G2-1.php?room=' + roomId;
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAXエラー: " + textStatus + ", " + errorThrown);
                });
            }

            function refreshData() {
                updateUsers();
                updateUserCount();
                updateRoleButtons();
                checkGameStart();
            }

            setInterval(refreshData, 1000); // 1秒ごとにデータを更新

            $('.role-button').click(function() {
                const roleId = $(this).data('role-id');
                const teamId = $(this).data('team-id');
                $.post('../update_users.php', {room_id: roomId, role_id: roleId, team_id: teamId}, function(response) {
                    alert(response);
                    refreshData();
                });
            });

            $('#startGame').click(function() {
                $.post('../start_game.php', {room_id: roomId}, function(response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message);
                    }
                });
            });

            $('#startGame').hide(); // 初期状態で隠しておく
        });
    </script>
</head>
<body>
    <h1>参加人数: <span id="userCount"><?php echo $userCount; ?></span></h1>
    <div id="userList">
        <?php
        foreach ($users as $user) {
            if ($user['team_ID'] !== null && $user['role_ID'] !== null) {
                echo "<p>{$teamNames[$user['team_ID']]} - {$roleNames[$user['role_ID']]} - {$user['user_name']}</p>";
            } else {
                echo "<p>{$user['user_name']}</p>";
            }
        }
        ?>
    </div>
    <?php if (!$is_host && empty($nickname)) { ?>
        <form method="post" action="">
            <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
            <label for="nickname">ニックネーム:</label>
            <input type="text" id="nickname" name="nickname" required>
            <button type="submit">参加</button>
        </form>
    <?php } ?>
    <div>
        <?php
        foreach ([1 => '赤チームオペレーター', 2 => '赤チームアストロノーツ', 3 => '青チームオペレーター', 4 => '青チームアストロノーツ'] as $index => $label) {
            $teamId = $index <= 2 ? 1 : 2;
            $roleId = $index % 2 == 1 ? 1 : 2;
            $disabled = false;
            foreach ($users as $user) {
                if ($user['team_ID'] == $teamId && $user['role_ID'] == $roleId) {
                    $disabled = true;
                    echo "<button class='role-button disabled-button' data-team-id='$teamId' data-role-id='$roleId' disabled>$label - {$user['user_name']}</button><br>";
                }
            }
            if (!$disabled) {
                echo "<button class='role-button' data-team-id='$teamId' data-role-id='$roleId'>$label</button><br>";
            }
        }
        ?>
    </div>
    <button id="startGame" style="display: none;">ゲームスタート</button>
</body>
</html>
