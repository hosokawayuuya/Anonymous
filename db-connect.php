<?php
    const SERVER = 'mysql304.phy.lolipop.lan';
    const DBNAME = 'LAA1517459-anonymous';
    const USER = 'LAA1517459';
    const PASS = 'Pass0515';

function connectDB() {
    $dsn = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';
    return new PDO($dsn, USER, PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}
?>