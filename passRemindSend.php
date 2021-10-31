<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　パスワード再発行認証キー送信ページ　　「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));
	
	$email = $_POST['email'];
	
	//未入力チェック
	validRequired($email,'email');
	
	if(empty($err_msg)) {
		//Email形式チェック
		validEmail($email,'email');
		//最大文字数チェック
		validMaxLen($email,'email');

		if(empty($err_msg)) {
			debug('バリデーションOK');
			//例外処理
			try {
				//DBへ接続
				$dbh = dbConnect();
				//クエリ作成
				$sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
				//流し込みデータ
				$data = array(':email' => $email);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				//クエリの結果を取得
				$rst = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if($stmt && array_shift($rst)) {
					debug('登録のあるEmailです');
					
					$_SESSION['msg_success'] = SUC03;
					//認証キーを生成
					$auth_key = makeRandKey();
					//メール送信
					$from = EMAIL_ADDRESS;
					$subject = 'パスワード再発行通知 | TraPos.JP';
					$to = $email;
					$comment = <<<EOT
当メールアドレス宛にパスワード再発行のご依頼がありました。
認証キーを下記、URLより入力して下さい。

認証キー：{$auth_key}
認証キー入力ページ：https://traposjp.hiro-app.net/passRemindReceive.php

※認証キーの有効期限は30分です。

===============================
TraPos.JP
URL：https://traposjp.hiro-app.net/index.php
==============================
EOT;
				sendMail($to,$subject,$comment,$from);
				
				//パスワード再発行に必要な情報をセッションへ格納
				$_SESSION['auth_key'] = $auth_key;
				$_SESSION['auth_key_limit'] = time() + (60*30);
				$_SESSION['auth_email'] = $email;
				
				debug('セッション変数の中身：'.print_r($_SESSION,true));
				debug('パスワード再発行認証キー入力ページへ遷移します');
				
				header('Location:passRemindReceive.php');
				}else {
					debug('登録のないEmailです');
					$err_msg['email'] = MSG12;
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
	
	<div class="l-content l-siteWidth">
		<main class="l-main">
			<div class="l-container l-container--line">
				<h1 class="c-siteTitle p-auth__title">パスワード再発行</h1>
				<p class="p-auth__announce">
					登録済みのメールアドレスを入力して下さい。<br>
					入力されたメールアドレスにパスワード再発行のメールをお送り致します。
				</p>
				<div class="u-attention u-attention--common">
					<?php echo getErrMsg('common'); ?>
				</div>
				<form action="" class="c-form p-auth" method="post">
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('email'); ?>">
						メールアドレス
						<input type="text" name="email" value="<?php echo getFormData('email'); ?>" class="c-form__inputText p-auth__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('email'); ?>
						</div>
					</label>
				
					<input type="submit" value="送信" class="c-btn c-btn--primary p-auth__login__btn">
					<a href="login.php" class="p-auth__signupLink">ログインはコチラ</a>
				</form>
			</div>
		</main>
	</div>
	
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>