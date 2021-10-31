<?php
//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　パスワード再発行認証キー入力ページ　　「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//セッションに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])) {
	debug('セッションに認証キーがありません。');
	
	header('Location:passRemindSend.php');
}
//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));
	
	$auth_key = $_POST['token'];
	
	//未入力チェック
	validRequired($auth_key,'token');
	
	if(empty($err_msg)) {
		//固定長チェック
		validLength($auth_key,'token');
		//半角英数字チェック
		validHalf($auth_key,'token');
		//セッションにある認証キーと一致するか
		if($_SESSION['auth_key'] != $auth_key) {
			$err_msg['token'] = MSG14;
		}
		//認証キーの有効期限チェック
		if($_SESSION['auth_key_limit'] < time()) {
			$err_msg['token'] = MSG15;
		}
		
		if(empty($err_msg)) {
			debug('バリデーションOK');
			//パスワードを生成
			$pass = makeRandKey();
			//例外処理
			try {
				//DBへ接続
				$dbh = dbConnect();
				//クエリ作成
				$sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
				//流し込みデータ
				$data = array(':email' => $_SESSION['auth_email'],
				 							':pass' => password_hash($pass, PASSWORD_DEFAULT));
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				
				if($stmt) {
					//メール送信
					$from = EMAIL_ADDRESS;
					debug('emaillllllllllll'.$from);
					$subject = 'パスワード再発行通知 | TraPos.JP';
					$to = $_SESSION['auth_email'];
					$comment = <<<EOT
パスワードを再発行しました。
下記URLよりログインを行って下さい。

パスワード：{$pass}
ログインページ：https://traposjp.hiro-app.net/login.php

※ログイン後、パスワードの変更を行って下さい。
===============================
TraPos.JP
URL：https://traposjp.hiro-app.net/index.php
==============================
EOT;
				sendMail($to,$subject,$comment,$from);
				
				//セッション変数を削除
				$_SESSION = array();
				
				debug('セッション変数の中身：'.print_r($_SESSION,true));
				debug('ログインページへ遷移します');
				
				header('Location:login.php');
			}else {
				$err_msg['common'] = MSG04;
			}
			} catch (\Exception $e) {
				error_log('エラー発生：'.$e->getMessage());
				$err_msg['common'] = MSG04;
			}
		}
	}
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
 ?>

<?php
$siteTitle = 'パスワード再発行';
require('head.php');
 ?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content">
		<main class="l-main">
			<div class="l-container l-container--line">
				<h1 class="c-siteTitle p-auth__title">パスワード再発行</h1>
				<p class="p-auth__announce">
					お送りしたメールへ記載の認証キーを入力して下さい。
					<span class="p-auth__announce__guide">
						※認証キーの有効期限は30分です
					</span>
				</p>
				<div class="u-attention u-attention--common">
					<?php echo getErrMsg('common'); ?>
				</div>
				<form action="" class="c-form p-auth" method="post">

					<label class="p-auth__label p-auth__label--line <?php echo appendClass('token'); ?>">
						認証キー
						<input type="text" name="token" value="<?php echo getFormData('token'); ?>" class="c-form__inputText p-auth__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('token'); ?>
						</div>
					</label>
				
					<input type="submit" value="送信" class="c-btn c-btn--primary p-auth__login__btn">
					<a href="passRemindSend.php" class="p-auth__signupLink">認証キーを再発行する場合はコチラ</a>
				</form>
			</div>
		</main>
	</div>

	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>