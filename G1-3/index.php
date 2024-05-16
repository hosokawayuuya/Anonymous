<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>リアルタイムユーザー数</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const roomId = "<?php echo htmlspecialchars($_GET['room']); ?>";
            // Update the user count every 5 seconds
            setInterval(function() {
                $.get('count_users.php', {room_id: roomId}, function(data) {
                    $('#userCount').text(data);
                });
            }, 5000);

            // Notify server of new connection
            $.post('update_users.php', {action: 'add', room_id: roomId});

            // Notify server of disconnect
            window.addEventListener('beforeunload', function() {
                navigator.sendBeacon('update_users.php', new URLSearchParams({action: 'remove', room_id: roomId}));
            });
        });
    </script>
</head>
<body>
    <h1>リアルタイムユーザー数</h1>
    <p>現在のユーザー数: <span id="userCount">読み込み中...</span></p>
    <form method="post" action="index.php">
        <label for="nickname">ニックネーム:</label>
        <input type="text" id="nickname" name="nickname" required>
        <button type="submit">参加</button>
    </form>
</body>
</html>
