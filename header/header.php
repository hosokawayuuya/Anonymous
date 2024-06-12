<?php require '../db-connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Anonymous</title>
</head>
<header>
        <h1>参加人数: <span id="userCount"><?php echo $userCount; ?></span></h1>
        <nav>
                <button onclick="location.href='kopyURL.php'">URL</button>
                <button onclick="location.href='rule.php'">ルール</button>
                <button onclick="location.href='reset2.php'">リセット</button>       
        </nav>
    </header>
<body>