<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　ログアウトページ　　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

debug('セッションを削除します');

//セッション変数を破棄
$_SESSION = array();

//クッキーを削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-1, '/');
}

//セッションIDを削除
session_destroy();

debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('ログインページへ遷移します');

header('Location:login.php');