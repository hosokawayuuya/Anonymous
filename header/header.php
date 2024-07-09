<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="header.css" rel="stylesheet">
    <title>Anonymous</title>
</head>
<body>
    <div class="container1">
        <header>
            <nav>
                    <h5>参加人数: <span id="userCount"><?php echo $userCount; ?></span></h5>
                    <button id="urlButton" onclick="togglePopup('urlPopup')">URL</button>
                    <button id="rulesButton" onclick="togglePopup('rulesPopup')">ルール</button>
                    <button id="resetButton" onclick="resetGame()">リセット</button>
            </nav>
        </header>
</div>
        <div id="rulesPopup" class="popup">
            <h1>ゲームのルール</h1>
            <h2>ゲームの基本設定</h2>
            <p>チーム分け：プレイヤーは2つのチーム（赤チームと青チーム）に分かれます。それぞれのチームには1人のオペレーターと数人のアストロノーツがいます。<br>カード配置：25枚のカードが5x5のグリッドに配置されます。それぞれのカードには1つの単語が書かれています。</p>
            <h2>ゲームの進行</h2>
            <p>ヒントを出す：各ラウンド、スパイマスターは自分のチームにヒントを1つ出します。ヒントは1つの単語と数字からなります（例：「動物 3」）。このヒントはチームの単語を推測するための手がかりとなります。</p>
            <p>推測：フィールドエージェントはスパイマスターのヒントに基づいて、カードを推測します。推測は1つずつ行い、カードをクリックして確認します。<br>・正しい単語（チームの色のカード）を当てた場合、推測を続けるかどうかを決めます。<br>・間違った単語（相手チームの色のカード、または中立カード）を当てた場合、そのターンは終了します。<br><br>ターンの終了：チームの推測が終わるとターンが終了し、次のチームに移ります。</p>
            <h2>勝利条件</h2>
            <p>全ての単語を当てる：先に全ての自分のチームの単語を正しく推測したチームが勝利します。<br><br>エイリアンを避ける：エイリアンの単語を避け続けることが重要です。エイリアンの単語を当てたチームは即座に敗北します。</p>
            
            <span class="close-button" onclick="closePopup('rulesPopup')">×</span>
        </div>

        <div id="urlPopup" class="popup">
            <div>
                <p>URL: <span id="websiteUrl"></span></p>
                <div class="center-button">
                    <button onclick="copyUrl()">URLをコピー</button>
                </div>
            </div>
            <span class="close-button" onclick="closePopup('urlPopup')">×</span>
        </div>

        <div id="overlay" class="overlay" onclick="closePopup()"></div>

        <div id="skillPopup" class="skill-popup">
            <span class="close-button" onclick="closePopup('skillPopup')">×</span>
        </div>
    </div>

    <script>
        function togglePopup(popupId) {
            var popup = document.getElementById(popupId);
            var overlay = document.getElementById('overlay');
            var websiteUrl = document.getElementById('websiteUrl');
            if (popupId === 'urlPopup') {
                websiteUrl.textContent = window.location.href;
            }
            popup.style.display = "block";
            overlay.style.display = "block";
        }

        function closePopup(popupId) {
            if (popupId) {
                document.getElementById(popupId).style.display = 'none';
            } else {
                document.querySelectorAll('.popup').forEach(function(popup) {
                    popup.style.display = 'none';
                });
            }
            document.getElementById('overlay').style.display = 'none';
        }

        function copyUrl() {
            var url = document.getElementById('websiteUrl').textContent;
            navigator.clipboard.writeText(url).then(function() {
                alert("URLがコピーされました: " + url);
            }, function(err) {
                console.error('コピーに失敗しました:', err);
            });
        }

        function resetGame() {
            if (confirm('本当にリセットしますか？')) {
                location.href = '../header/reset2.php';
            }
        }

        document.getElementById('nextButton').addEventListener('click', function() {
            var currentPopup = document.querySelector('.popup:not([style*="display: none"])');
            if (currentPopup.id === 'rulesPopup') {
                currentPopup.style.display = 'none';
                document.getElementById('skillPopup').style.display = 'block';
            } else if (currentPopup.id === 'skillPopup') {
                // 他のポップアップがあればここに追加
            }
        });

        document.getElementById('backButton').addEventListener('click', function() {
            var currentPopup = document.querySelector('.popup:not([style*="display: none"])');
            if (currentPopup.id === 'skillPopup') {
                currentPopup.style.display = 'none';
                document.getElementById('rulesPopup').style.display = 'block';
            }
        });

        var closeButtons = document.querySelectorAll('.close-button');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('rulesPopup').style.display = 'none';
                document.getElementById('urlPopup').style.display = 'none';
                document.getElementById('skillPopup').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
            });
        });

        document.getElementById('backButton').addEventListener('click', function() {
            document.getElementById('skillPopup').style.display = 'none';
            document.getElementById('rulesPopup').style.display = 'block';
        });
    </script>  
</body>
</html>