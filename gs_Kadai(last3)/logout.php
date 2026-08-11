<?php
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //Logout処理
}

//1. サーバ側でSESSIONの中身を消す SESSION変数を空にする
$_SESSION = [];

//2. ブラウザ側でcookieの値を消す
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

//3. サーバ側でセッション自体を破棄
session_destroy();

header("Location: display.php");
exit();
?>