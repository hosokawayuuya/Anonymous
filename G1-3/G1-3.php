<?php
session_start();
require '../db-connect.php';

$room_key = $_GET['room_key'] ?? '';
$room_id = $_GET['room_id'] ?? '';
$room_id = $_GET['room'] ?? '';
$nickname = $_SESSION['nickname'] ?? '';
$is_host = $_SESSION['is_host'] ?? false;
$users = [];
$userCount = 0;
$roomStatus = '';


if (empty($room_id) && !empty($room_key)) {
    // room_keyを使ってroom_IDを取得
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT room_ID FROM Room WHERE room_key = ?");
        $stmt->execute([$room_key]);
        $room_id = $stmt->fetchColumn();

        if (!$room_id) {
            throw new Exception('無効なルームキーです。');
        }
    } catch (Exception $e) {
        echo 'エラー: ' . $e->getMessage();
        exit();
    } catch (PDOException $e) {
        echo 'データベース接続エラー: ' . $e->getMessage();
        exit();
    }
}

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

    // 役割ごとのユーザー情報を取得
    $stmt = $pdo->prepare("SELECT user_name, team_ID, role_ID FROM User WHERE room_ID = ? AND role_ID IS NOT NULL");
    $stmt->execute([$room_id]);
    $roleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="G1-3ver2.0.css">
    <link rel="stylesheet" href="styles.css"> <!-- 追加 -->
    <title>Anonymous</title>
</head>

<body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        const roomId = "<?php echo htmlspecialchars($room_id); ?>";

        function updateUsers() {
            $.get('get_users.php', {room_id: roomId}, function(data) {
                $('#userList').html(data);
                updateRoleButtons();
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

        function updateRoleUsers() {
            $.get('get_role_users.php', {room_id: roomId}, function(data) {
                const roles = JSON.parse(data);
                $('#operator-red').html('');
                $('#astronaut-red').html('');
                $('#operator-blue').html('');
                $('#astronaut-blue').html('');

                roles.forEach(function(user) {
                    if (user.team_ID == 1 && user.role_ID == 1) {
                        $('#operator-red').append('<p>' + user.user_name + '</p>');
                    } else if (user.team_ID == 1 && user.role_ID == 2) {
                        $('#astronaut-red').append('<p>' + user.user_name + '</p>');
                    } else if (user.team_ID == 2 && user.role_ID == 1) {
                        $('#operator-blue').append('<p>' + user.user_name + '</p>');
                    } else if (user.team_ID == 2 && user.role_ID == 2) {
                        $('#astronaut-blue').append('<p>' + user.user_name + '</p>');
                    }
                });
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
            updateRoleUsers();
        }

        setInterval(refreshData, 1000);

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

        $('#startGame').hide();
    });
</script>
<div class="Name">
    <div class="namebox">
        <?php if (!$is_host && empty($nickname)) { ?>
            <form method="post" action="">
                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
                <label for="nickname">ニックネーム:</label>
                <input type="text" id="nickname" name="nickname" required>
                <button type="submit">参加</button>
            </form>
        <?php } else {
            echo "<p>参加者: $nickname</p>";
        } ?>
    </div>
</div>

<div class="container">
    <div class="team-box red-team">
        <h2>赤チーム</h2>
        <div class="photo-container">
            <img src="../img/universe3.jpg" alt="赤チーム写真" class="team-photo">
            <span class="number">9</span>
        </div>
        <div class="team-info">
            <button class="role-button" data-team-id="1" data-role-id="1">オペレーター</button>
            <div id="operator-red"></div>
            <br>
            <button class="role-button" data-team-id="1" data-role-id="2">アストロノーツ</button>
            <div id="astronaut-red"></div>
        </div>
    </div>

    <button id="startGame" style="display: none;">ゲームスタート</button>

    <div class="info-box">
        <h1>ルール: <span id="userCount"><?php echo $userCount; ?></span></h1>
        <div id="userList">
            <?php foreach ($users as $user) {
                echo "<p>{$user['user_name']}</p>";
            } ?>
        </div>
    </div>

    <div class="team-box blue-team">
        <h2>青チーム</h2>
        <div class="photo-container">
            <img src="../img/universe4.jpg" alt="青チーム写真" class="team-photo">
            <span class="number">9</span>
        </div>
        <div class="team-info">
            <button class="role-button" data-team-id="2" data-role-id="1">オペレーター</button>
            <div id="astronaut-blue"></div>
            <br>
            <button class="role-button" data-team-id="2" data-role-id="2">アストロノーツ</button>
            <div id="astronaut-blue"></div>
        </div>
    </div>
</div>

</body>
</html>
